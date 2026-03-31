<?php

namespace FSPoster\App\Providers\Common;

use FSPoster\App\Models\ChannelSession;
use FSPoster\App\Models\Schedule;
use FSPoster\App\Models\Channel;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use FSPoster\App\Providers\Schedules\ScheduleShareException;
use Exception;

class ShortCodeServiceForWorkflow
{
    private $shortCodeCategories = [];

    private $replacers = [];

    private $lazyShortCodeCallbacks = [];

    public function addReplacer($replacer)
    {
        if (is_callable($replacer)) {
            $this->replacers[] = $replacer;
        }

        return $this;
    }

    public function registerShortCodesLazily($callback): self
    {
        if (is_callable($callback)) {
            $this->lazyShortCodeCallbacks[] = $callback;
        }

        return $this;
    }

    /**
     * @param array $data
     */
    public function replace($text, $data)
    {
        $text = apply_filters('fsp_short_code_before_replace', $text, $data);

        foreach ($this->replacers as $replacer) {
            $text = $replacer($text, $data, $this);
        }

        return apply_filters('fsp_short_code_after_replace', $text, $data);
    }

    public function getShortCodesList($filterByDependsParameter = [], $filterByKind = [], $groupByCategory = false): array
    {
        foreach ($this->lazyShortCodeCallbacks as $callback) {
            $callback($this);
        }
        $this->lazyShortCodeCallbacks = [];

        $filteredShortCodesList = [];
        foreach ($this->shortCodeCategories as $category => $shortCodeCategoryInfo) {
            $categoryName = $shortCodeCategoryInfo['name'];
            $shortCodesList = $shortCodeCategoryInfo['short_codes'];

            foreach ($shortCodesList as $shortCodeInf) {
                if (
                    (empty($filterByDependsParameter) || empty($shortCodeInf['depends']) || in_array($shortCodeInf['depends'], $filterByDependsParameter)) &&
                    (empty($filterByKind) || in_array($shortCodeInf['kind'], $filterByKind, true))
                ) {
                    if ($groupByCategory) {
                        if (!isset($filteredShortCodesList[$category])) {
                            $filteredShortCodesList[$category] = [
                                'name' => $categoryName,
                                'short_codes' => []
                            ];
                        }

                        $filteredShortCodesList[$category]['short_codes'][] = $shortCodeInf;
                    } else {
                        $filteredShortCodesList[] = $shortCodeInf;
                    }
                }
            }
        }

        return $filteredShortCodesList;
    }

    public function registerCategory($shortCodeCategory, $name): void
    {
        if (!isset($this->shortCodeCategories[$shortCodeCategory])) {
            $this->shortCodeCategories[$shortCodeCategory] = ['short_codes' => []];
        }

        $this->shortCodeCategories[$shortCodeCategory]['name'] = $name;
    }

    public function registerShortCode($shortCode, $params = []): void
    {
        $defaultParams = [
            'name' => '',
            'description' => '',
            'category' => 'others',
            'depends' => '',
            'kind' => ''
        ];
        $params['code'] = $shortCode;
        $params = array_merge($defaultParams, $params);
        $shortCodeCategory = $params['category'];

        if (!isset($this->shortCodeCategories[$shortCodeCategory])) {
            $this->registerCategory($shortCodeCategory, $shortCodeCategory);
        }

        $this->shortCodeCategories[$shortCodeCategory]['short_codes'][] = $params;
    }

    public function replaceShortCodes($text, $data)
    {
        try {
            $scheduleObj = new ScheduleObject($data['schedule_id']);
            $schedule = $scheduleObj->getSchedule();

            $arr = [
                '{schedule_id}' => $schedule->id ?? null,
                '{schedule_owner}' => $schedule->user_id ?? null,
                '{wp_post_id}' => $schedule->wp_post_id ?? null,
                '{schedule_created_at}' => $schedule->created_at ? Date::dateTime($schedule->created_at) : null,
                '{send_time}' => $schedule->send_time ?? null,
                '{schedule_status}' => $schedule->status ?? null,
                '{schedule_error_message}' => $schedule->error_msg ?? '',
                '{wp_post_link}' => $scheduleObj->getPostOriginalUrl() ?? '',
                '{wp_post_title}' => $scheduleObj->getWPPost()->post_title ?? '',
                '{channel_id}' => $schedule->channel_id ?? null,
                '{channel_name}' => $scheduleObj->getChannel() ? $scheduleObj->getChannel()->name : null,
                '{social_network}' => $scheduleObj->getSocialNetwork(),
            ];

            $text = str_replace(array_keys($arr), array_values($arr), $text);
        } catch (Exception|ScheduleShareException $e) {}

        return $text;
    }
}
