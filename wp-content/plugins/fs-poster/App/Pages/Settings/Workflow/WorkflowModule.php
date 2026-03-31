<?php

namespace FSPoster\App\Pages\Settings\Workflow;

use FSPoster\App\Pages\ChannelLabel\Services\ChannelLabelService;
use FSPoster\App\Pages\Notification\Registerer\NotificationWorkflowEventRegisterer;
use FSPoster\App\Pages\Notification\Registerer\NotificationWorkflowEvents\ScheduleFailedNotificationWorkflowEvent;
use FSPoster\App\Pages\Settings\Workflow\Actions\InAppNotification;
use FSPoster\App\Pages\Settings\Workflow\Controllers\WorkflowActionController;
use FSPoster\App\Pages\Settings\Workflow\Controllers\WorkflowController;
use FSPoster\App\Pages\Settings\Workflow\Controllers\WorkflowEventsController;
use FSPoster\App\Pages\Settings\Workflow\Controllers\WorkflowLogController;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkflowLogRequest;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowActionResponse;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowLogResponse;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowActionMapper;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowLogMapper;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowMapper;
use FSPoster\App\Pages\Settings\Workflow\Repositories\WorkflowActionRepository;
use FSPoster\App\Pages\Settings\Workflow\Repositories\WorkflowLogRepository;
use FSPoster\App\Pages\Settings\Workflow\Repositories\WorkflowRepository;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowActionService;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowLogService;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowService;
use FSPoster\App\Providers\Common\ShortCodeServiceForWorkflow;
use FSPoster\App\Providers\Common\WorkflowDriversManager;
use FSPoster\App\Providers\Common\WorkflowEventsManager;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use ReflectionException;

class WorkflowModule
{
    /**
     * @throws ReflectionException
     */
    public static function init(): void
    {
        self::registerDependencies();
        self::registerCoreShortCodes();

        $shortCodeService = Container::get(ShortCodeServiceForWorkflow::class);
        $workflowDriverManager = Container::get(WorkflowDriversManager::class);
        $workflowEventsManager = Container::get(WorkflowEventsManager::class);

        self::registerCoreWorkflowEvents();

        $workflowEventsManager->setDriverManager($workflowDriverManager);
        $workflowEventsManager->setShortcodeService($shortCodeService);

        self::registerShortCodes();
        self::registerHooks();
        self::registerCoreWorkflowActions();
    }

    public static function registerDependencies(): void
    {
        Container::addBulk([
            WorkflowController::class,
            WorkflowEventsManager::class,
            WorkflowService::class,
            WorkflowRepository::class,
            WorkflowActionService::class,
            WorkflowActionRepository::class,
            WorkflowDriversManager::class,
            WorkflowActionController::class,
            WorkflowEventsController::class,
            ShortCodeServiceForWorkflow::class,
            WorkflowLogController::class,
            WorkflowLogService::class,
            WorkflowLogRepository::class,
            WorkflowLogMapper::class,
            WorkflowMapper::class,
            InAppNotification::class,
            WorkflowActionMapper::class,
        ]);

        Container::addTransient(WorkflowActionResponse::class);
        Container::addTransient(WorkflowLogResponse::class);
        Container::addTransient(WorkflowLogRequest::class);
    }

    public static function registerHooks(): void
    {
        add_action('fsp_schedule_failed', [self::class, 'scheduleFailedWorkflow'], 1000, 1);
    }

    public static function registerCoreShortCodes(): void
    {
        $shortCodeService = Container::get(ShortCodeServiceForWorkflow::class);
        $shortCodeService->addReplacer([$shortCodeService, 'replaceShortCodes']);
    }

    /**
     * @throws ReflectionException
     */
    public static function registerCoreWorkflowEvents(): void
    {
        $workflowEventsManager = Container::get(WorkflowEventsManager::class);

        $workflowEventsManager->get('schedule_failed')
            ->setTitle(fsp__('Schedule Failed'))
            ->setAvailableParams(['schedule_id', 'error_message', 'schedule_owner']);

        NotificationWorkflowEventRegisterer::registerEvents('schedule_failed', ScheduleFailedNotificationWorkflowEvent::class);
    }

    /**
     * @throws ReflectionException
     */
    public static function registerShortCodes(): void
    {
        $shortCodeService = Container::get(ShortCodeServiceForWorkflow::class);

        $shortCodeService->registerShortCode('schedule_id', [
            'name'      =>  fsp__('Schedule Id'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
            'kind'      =>  'schedule_id'
        ]);

        $shortCodeService->registerShortCode('schedule_owner', [
            'name'      =>  fsp__('Schedule Owner'),
            'category'  =>  'owner',
            'depends'   =>  'schedule_owner',
        ]);

        $shortCodeService->registerShortCode('wp_post_id', [
            'name'      =>  fsp__('WordPress Post ID'),
            'category'  =>  'post_id',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('schedule_created_at', [
            'name'      =>  fsp__('Schedule created'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('send_time', [
            'name'      =>  fsp__('Send time'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('schedule_status', [
            'name'      =>  fsp__('Schedule Status'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('schedule_error_message', [
            'name'      =>  fsp__('Schedule error message'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('wp_post_link', [
            'name'      =>  fsp__('Wordpress post link'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('wp_post_title', [
            'name'      =>  fsp__('Wordpress post title'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('channel_id', [
            'name'      =>  fsp__('Channel Id'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('channel_name', [
            'name'      =>  fsp__('Channel name'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);

        $shortCodeService->registerShortCode('social_network', [
            'name'      =>  fsp__('Social Network'),
            'category'  =>  'schedule',
            'depends'   =>  'schedule_id',
        ]);
    }

    public static function registerCoreWorkflowActions(): void
    {
        $drivers = Container::get(WorkflowDriversManager::class);
        $drivers->register(Container::get(InAppNotification::class));
    }

    /**
     * @throws ReflectionException
     */
    public static function scheduleFailedWorkflow(ScheduleObject $scheduleObj): void
    {
        $eventManager = Container::get(WorkflowEventsManager::class);

        $eventManager->trigger('schedule_failed', [
            'schedule_id' => $scheduleObj->getSchedule()->id
        ], function ($event) use ($scheduleObj) {
            if (empty($event->data)) {
                return true;
            }

            $data = json_decode($event->data, true);

            if (isset($data['channels']) && count($data['channels']) > 0 && !in_array($scheduleObj->getChannel()->id, $data['channels'])) {
                return false;
            }

            if (isset($data['networks']) && count($data['networks']) > 0 && !in_array($scheduleObj->getSocialNetwork(), $data['networks'])) {
                return false;
            }

            if (isset($data['channelLabels']) && (count($data['channelLabels']) > 0)) {

                $channelLabelService = Container::get(ChannelLabelService::class);

                $channelLabelCount = $channelLabelService->countByChannelAndLabelIds($scheduleObj->getChannel()->id, $data['channelLabels']);

                if ($channelLabelCount === 0) {
                    return false;
                }
            }

            return true;
        });
    }
}
