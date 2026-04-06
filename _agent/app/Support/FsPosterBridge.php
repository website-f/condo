<?php

namespace App\Support;

use App\Models\CondoListing;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FsPosterBridge
{
    /**
     * @return \Illuminate\Support\Collection<int, array{id:int,name:string,channel_type:string,social_network:string,status:bool,auto_share:bool,picture:string,session_name:string,created_by:int}>
     */
    public function availableChannels(): Collection
    {
        return collect(DB::connection('condo')
            ->table('fsp_channels')
            ->join('fsp_channel_sessions', 'fsp_channel_sessions.id', '=', 'fsp_channels.channel_session_id')
            ->where('fsp_channels.is_deleted', 0)
            ->where('fsp_channels.status', 1)
            ->orderBy('fsp_channel_sessions.social_network')
            ->orderBy('fsp_channels.name')
            ->get([
                'fsp_channels.id',
                'fsp_channels.name',
                'fsp_channels.channel_type',
                'fsp_channels.picture',
                'fsp_channels.auto_share',
                'fsp_channels.status',
                'fsp_channel_sessions.social_network',
                'fsp_channel_sessions.name as session_name',
                'fsp_channel_sessions.created_by',
            ]))
            ->map(fn (object $channel) => [
                'id' => (int) $channel->id,
                'name' => trim((string) $channel->name) !== '' ? trim((string) $channel->name) : '[no name]',
                'channel_type' => (string) $channel->channel_type,
                'social_network' => (string) $channel->social_network,
                'status' => (int) $channel->status === 1,
                'auto_share' => (int) $channel->auto_share === 1,
                'picture' => (string) $channel->picture,
                'session_name' => (string) $channel->session_name,
                'created_by' => (int) $channel->created_by,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id:int,title:string,formatted_price:string,source_key:string,image_url:?string}>
     */
    public function availableListings(string $username): Collection
    {
        return $this->ownedListings($username)
            ->sortByDesc(fn (CondoListing $listing) => (string) $listing->updateddate)
            ->values()
            ->map(fn (CondoListing $listing) => [
                'id' => (int) $listing->getKey(),
                'title' => (string) $listing->propertyname,
                'formatted_price' => (string) $listing->formatted_price,
                'source_key' => 'condo',
                'image_url' => $listing->image_url,
            ]);
    }

    /**
     * @return Collection<int, array{
     *     group_id:string,
     *     listing_id:int,
     *     listing_title:string,
     *     listing_url:string,
     *     scheduled_at:Carbon,
     *     scheduled_at_form:string,
     *     status:string,
     *     status_label:string,
     *     status_color:string,
     *     message:string,
     *     channel_ids:array<int, int>,
     *     social_networks:array<int, string>,
     *     channels:array<int, array{id:int,name:string,social_network:string,channel_type:string,picture:string}>,
     *     error_messages:array<int, string>,
     *     is_mutable:bool,
     *     total_channels:int
     * }>
     */
    public function scheduleGroupsForAgent(string $username): Collection
    {
        $rows = collect(DB::connection('condo')
            ->table('fsp_schedules')
            ->join('posts', 'posts.ID', '=', 'fsp_schedules.wp_post_id')
            ->join('postmeta', function ($join) {
                $join->on('postmeta.post_id', '=', 'posts.ID')
                    ->where('postmeta.meta_key', '=', CondoWordpressBridge::META_USERNAME);
            })
            ->join('fsp_channels', 'fsp_channels.id', '=', 'fsp_schedules.channel_id')
            ->join('fsp_channel_sessions', 'fsp_channel_sessions.id', '=', 'fsp_channels.channel_session_id')
            ->where('posts.post_type', 'properties')
            ->where('postmeta.meta_value', $username)
            ->orderByDesc('fsp_schedules.send_time')
            ->get([
                'fsp_schedules.id',
                'fsp_schedules.group_id',
                'fsp_schedules.wp_post_id',
                'fsp_schedules.channel_id',
                'fsp_schedules.user_id',
                'fsp_schedules.status',
                'fsp_schedules.error_msg',
                'fsp_schedules.send_time',
                'fsp_schedules.customization_data',
                'posts.post_title',
                'posts.guid',
                'fsp_channels.name',
                'fsp_channels.picture',
                'fsp_channels.channel_type',
                'fsp_channel_sessions.social_network',
            ]));

        return $rows
            ->groupBy('group_id')
            ->map(fn (Collection $groupRows) => $this->hydrateScheduleGroup($groupRows))
            ->sortByDesc(fn (array $group) => $group['scheduled_at']->timestamp)
            ->values();
    }

    /**
     * @return array{
     *     group_id:string,
     *     listing_id:int,
     *     listing_title:string,
     *     listing_url:string,
     *     scheduled_at:Carbon,
     *     scheduled_at_form:string,
     *     status:string,
     *     status_label:string,
     *     status_color:string,
     *     message:string,
     *     channel_ids:array<int, int>,
     *     social_networks:array<int, string>,
     *     channels:array<int, array{id:int,name:string,social_network:string,channel_type:string,picture:string}>,
     *     error_messages:array<int, string>,
     *     is_mutable:bool,
     *     total_channels:int
     * }
     */
    public function findScheduleGroupForAgent(string $username, string $groupId): array
    {
        $group = $this->scheduleGroupsForAgent($username)->firstWhere('group_id', $groupId);

        if ($group === null) {
            abort(404);
        }

        return $group;
    }

    public function storeScheduleGroup(CondoListing $listing, string $username, array $validated, ?string $groupId = null): string
    {
        $groupId = $groupId ?: (string) Str::uuid();
        $scheduledAt = Carbon::parse((string) $validated['scheduled_at'], config('app.timezone'))->format('Y-m-d H:i:s');
        $channelIds = collect($validated['channel_ids'] ?? [])->map(fn (mixed $id) => (int) $id)->unique()->values();
        $channels = $this->availableChannels()->whereIn('id', $channelIds)->values();
        $previousPostIds = DB::connection('condo')
            ->table('fsp_schedules')
            ->where('group_id', $groupId)
            ->pluck('wp_post_id')
            ->map(fn (mixed $id) => (int) $id)
            ->unique()
            ->values();

        if ($channels->count() !== $channelIds->count()) {
            throw ValidationException::withMessages([
                'channel_ids' => 'Choose only active FS Poster channels.',
            ]);
        }

        $postId = (int) $listing->getKey();
        $userId = $this->resolveWordpressUserId($username);

        DB::connection('condo')->transaction(function () use ($groupId, $scheduledAt, $channels, $validated, $postId, $userId, $previousPostIds) {
            DB::connection('condo')
                ->table('fsp_schedules')
                ->where('group_id', $groupId)
                ->delete();

            foreach ($channels as $channel) {
                DB::connection('condo')->table('fsp_schedules')->insert([
                    'blog_id' => 1,
                    'wp_post_id' => $postId,
                    'user_id' => $userId,
                    'channel_id' => $channel['id'],
                    'status' => 'not_sent',
                    'error_msg' => null,
                    'send_time' => $scheduledAt,
                    'remote_post_id' => null,
                    'visit_count' => 0,
                    'planner_id' => 0,
                    'data' => json_encode([], JSON_UNESCAPED_SLASHES),
                    'customization_data' => json_encode(
                        $this->customizationDataForChannel($channel, (string) ($validated['message'] ?? '')),
                        JSON_UNESCAPED_SLASHES
                    ),
                    'group_id' => $groupId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->upsertMeta($postId, 'fsp_enable_auto_share', '1');
            $this->upsertMeta($postId, 'fsp_schedule_group_id', $groupId);
            $this->upsertMeta($postId, 'fsp_schedule_created_manually', '1');
            $this->deleteMeta($postId, 'fsp_cache_schedules_data');

            foreach ($previousPostIds as $previousPostId) {
                if ($previousPostId === $postId) {
                    continue;
                }

                $currentGroupId = DB::connection('condo')
                    ->table('postmeta')
                    ->where('post_id', $previousPostId)
                    ->where('meta_key', 'fsp_schedule_group_id')
                    ->value('meta_value');

                if ($currentGroupId === $groupId) {
                    $this->deleteMeta($previousPostId, 'fsp_schedule_group_id');
                    $this->deleteMeta($previousPostId, 'fsp_enable_auto_share');
                    $this->deleteMeta($previousPostId, 'fsp_schedule_created_manually');
                }
            }
        });

        return $groupId;
    }

    public function deleteScheduleGroup(string $username, string $groupId): void
    {
        $group = $this->findScheduleGroupForAgent($username, $groupId);
        $postId = $group['listing_id'];

        DB::connection('condo')->transaction(function () use ($groupId, $postId) {
            DB::connection('condo')
                ->table('fsp_schedules')
                ->where('group_id', $groupId)
                ->delete();

            $currentGroupId = DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', 'fsp_schedule_group_id')
                ->value('meta_value');

            if ($currentGroupId === $groupId) {
                $replacementGroupId = DB::connection('condo')
                    ->table('fsp_schedules')
                    ->where('wp_post_id', $postId)
                    ->orderByDesc('send_time')
                    ->value('group_id');

                if (is_string($replacementGroupId) && trim($replacementGroupId) !== '') {
                    $this->upsertMeta($postId, 'fsp_schedule_group_id', $replacementGroupId);
                } else {
                    $this->deleteMeta($postId, 'fsp_schedule_group_id');
                    $this->deleteMeta($postId, 'fsp_enable_auto_share');
                    $this->deleteMeta($postId, 'fsp_schedule_created_manually');
                }
            }
        });
    }

    /**
     * @param  array{id:int,name:string,channel_type:string,social_network:string}  $channel
     * @return array<string, mixed>
     */
    private function customizationDataForChannel(array $channel, string $message): array
    {
        $socialNetwork = $channel['social_network'];
        $defaults = match ($socialNetwork) {
            'instagram' => $this->instagramDefaults($channel['channel_type']),
            'linkedin' => $this->defaultAttachableMediaDefaults('linkedin'),
            'threads' => $this->defaultAttachableMediaDefaults('threads'),
            'google_b' => $this->googleBusinessDefaults(),
            'tumblr' => $this->tumblrDefaults(),
            'tiktok' => $this->tiktokDefaults(),
            'pinterest' => $this->pinterestDefaults(),
            'blogger' => $this->bloggerDefaults(),
            'fb' => $this->facebookDefaults($channel['channel_type']),
            default => [
                'post_content' => '{post_title}',
            ],
        };

        if (trim($message) !== '') {
            $defaults['post_content'] = trim($message);
        }

        return $defaults;
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array{
     *     group_id:string,
     *     listing_id:int,
     *     listing_title:string,
     *     listing_url:string,
     *     scheduled_at:Carbon,
     *     scheduled_at_form:string,
     *     status:string,
     *     status_label:string,
     *     status_color:string,
     *     message:string,
     *     channel_ids:array<int, int>,
     *     social_networks:array<int, string>,
     *     channels:array<int, array{id:int,name:string,social_network:string,channel_type:string,picture:string}>,
     *     error_messages:array<int, string>,
     *     is_mutable:bool,
     *     total_channels:int
     * }
     */
    private function hydrateScheduleGroup(Collection $rows): array
    {
        $first = $rows->first();
        $scheduledAt = Carbon::parse((string) $first->send_time, config('app.timezone'));
        $statuses = $rows->pluck('status')->map(fn (mixed $status) => (string) $status)->unique()->values()->all();
        $status = $this->displayStatus($statuses);
        $message = collect(json_decode((string) $first->customization_data, true) ?: [])
            ->get('post_content', '');

        return [
            'group_id' => (string) $first->group_id,
            'listing_id' => (int) $first->wp_post_id,
            'listing_title' => trim((string) $first->post_title) !== '' ? trim((string) $first->post_title) : 'Untitled condo listing',
            'listing_url' => trim((string) $first->guid),
            'scheduled_at' => $scheduledAt,
            'scheduled_at_form' => $scheduledAt->format('Y-m-d\TH:i'),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_color' => $this->statusColor($status),
            'message' => trim((string) $message),
            'channel_ids' => $rows->pluck('channel_id')->map(fn (mixed $id) => (int) $id)->unique()->values()->all(),
            'social_networks' => $rows->pluck('social_network')->map(fn (mixed $value) => (string) $value)->unique()->values()->all(),
            'channels' => $rows->map(fn (object $row) => [
                'id' => (int) $row->channel_id,
                'name' => trim((string) $row->name) !== '' ? trim((string) $row->name) : '[no name]',
                'social_network' => (string) $row->social_network,
                'channel_type' => (string) $row->channel_type,
                'picture' => (string) $row->picture,
            ])->unique('id')->values()->all(),
            'error_messages' => $rows->pluck('error_msg')->map(fn (mixed $value) => trim((string) $value))->filter()->unique()->values()->all(),
            'is_mutable' => $scheduledAt->isFuture() && collect($statuses)->every(fn (string $rowStatus) => in_array($rowStatus, ['not_sent', 'draft'], true)),
            'total_channels' => $rows->pluck('channel_id')->unique()->count(),
        ];
    }

    /**
     * @return Collection<int, CondoListing>
     */
    private function ownedListings(string $username): Collection
    {
        return CondoListing::query()
            ->active()
            ->with('details')
            ->get()
            ->filter(fn (CondoListing $listing) => $listing->username === $username)
            ->values();
    }

    /**
     * @param  array<int, string>  $statuses
     */
    private function displayStatus(array $statuses): string
    {
        if (in_array('sending', $statuses, true)) {
            return 'sending';
        }

        if ($statuses === ['success']) {
            return 'success';
        }

        if ($statuses === ['error']) {
            return 'error';
        }

        if ($statuses === ['draft']) {
            return 'draft';
        }

        if ($statuses === ['not_sent']) {
            return 'scheduled';
        }

        if (in_array('error', $statuses, true)) {
            return 'mixed';
        }

        if (in_array('success', $statuses, true) && in_array('not_sent', $statuses, true)) {
            return 'mixed';
        }

        return 'scheduled';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'success' => 'Sent',
            'error' => 'Failed',
            'draft' => 'Draft',
            'sending' => 'Sending',
            'mixed' => 'Mixed',
            default => 'Scheduled',
        };
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'success' => '#14833b',
            'error' => '#d92d20',
            'draft' => '#995c00',
            'sending' => '#0f6bdc',
            'mixed' => '#7c3aed',
            default => '#1d1d1f',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function facebookDefaults(string $channelType): array
    {
        $isStory = in_array($channelType, ['account_story', 'ownpage_story'], true);

        return [
            'attach_link' => $isStory ? $this->boolOption('fb_story_attach_link', false) : $this->boolOption('fb_post_attach_link', true),
            'upload_media' => $isStory ? true : $this->boolOption('fb_post_upload_media', false),
            'upload_media_type' => $isStory ? 'featured_image' : $this->stringOption('fb_post_media_type_to_upload', 'featured_image'),
            'post_content' => $isStory ? $this->stringOption('fb_story_text', '{post_title}') : $this->stringOption('fb_post_text', '{post_title}'),
        ] + ($this->boolOption('fb_share_to_first_comment', false)
            ? ['first_comment' => $this->stringOption('fb_first_comment_text', '')]
            : []);
    }

    /**
     * @return array<string, mixed>
     */
    private function instagramDefaults(string $channelType): array
    {
        $isStory = $channelType === 'account_story';
        $defaults = [
            'attach_link' => $isStory ? $this->boolOption('instagram_story_send_link', false) : false,
            'upload_media' => true,
            'upload_media_type' => $isStory ? 'featured_image' : $this->stringOption('instagram_media_type_to_upload', 'featured_image'),
            'post_content' => $isStory ? $this->stringOption('instagram_story_text', '{post_title}') : $this->stringOption('instagram_post_text', '{post_title}'),
        ];

        if ($isStory && $this->boolOption('instagram_story_customization_add_hashtag', false)) {
            $defaults['story_hashtag'] = $this->stringOption('instagram_story_customization_hashtag_text', '');
        }

        if (! $isStory) {
            $defaults['pin_the_post'] = $this->boolOption('instagram_pin_the_post', false);

            if ($this->boolOption('instagram_share_to_first_comment', false)) {
                $defaults['first_comment'] = $this->stringOption('instagram_first_comment_text', '');
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultAttachableMediaDefaults(string $prefix): array
    {
        return [
            'attach_link' => $this->boolOption($prefix . '_attach_link', true),
            'upload_media' => $this->boolOption($prefix . '_upload_media', false),
            'upload_media_type' => $this->stringOption($prefix . '_media_type_to_upload', 'featured_image'),
            'post_content' => $this->stringOption($prefix . '_post_content', '{post_title}'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function googleBusinessDefaults(): array
    {
        return [
            'attach_link' => $this->boolOption('google_b_add_button', true),
            'attach_link_type' => $this->stringOption('google_b_button_type', 'LEARN_MORE'),
            'upload_media' => $this->boolOption('google_b_upload_media', false),
            'upload_media_type' => $this->stringOption('google_b_media_type_to_upload', 'featured_image'),
            'post_content' => $this->stringOption('google_b_post_content', '{post_title}'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tumblrDefaults(): array
    {
        return [
            'post_title' => $this->stringOption('tumblr_post_title', '{post_title}'),
            'send_tags' => $this->boolOption('tumblr_send_tags', false),
            'custom_tags' => [],
            'attach_link' => $this->boolOption('tumblr_attach_link', true),
            'upload_media' => $this->boolOption('tumblr_upload_media', false),
            'upload_media_type' => $this->stringOption('tumblr_media_type_to_upload', 'featured_image'),
            'post_content' => $this->stringOption('tumblr_post_content', '{post_content}'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tiktokDefaults(): array
    {
        return [
            'upload_media' => true,
            'upload_media_type' => 'featured_image',
            'privacy_level' => $this->stringOption('tiktok_privacy_level', 'PUBLIC_TO_EVERYONE'),
            'disable_duet' => $this->boolOption('tiktok_disable_duet', false),
            'disable_comment' => $this->boolOption('tiktok_disable_comment', false),
            'disable_stitch' => $this->boolOption('tiktok_disable_stitch', false),
            'auto_add_music_to_photo' => $this->boolOption('tiktok_auto_add_music_to_photo', true),
            'post_content' => $this->stringOption('tiktok_post_content', '{post_title}'),
            'photo_title' => $this->stringOption('tiktok_photo_title', '{post_title}'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pinterestDefaults(): array
    {
        return [
            'post_title' => $this->stringOption('pinterest_post_title', '{post_title}'),
            'alt_text' => $this->stringOption('pinterest_alt_text', ''),
            'attach_link' => $this->boolOption('pinterest_attach_link', true),
            'upload_media_type' => 'all_images',
            'post_content' => $this->stringOption('pinterest_post_content', '{post_content limit="497"}'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bloggerDefaults(): array
    {
        return [
            'is_draft' => $this->stringOption('blogger_post_status', 'draft') === 'draft',
            'kind' => '',
            'send_pages_as_page' => $this->boolOption('blogger_send_pages_as_page', true),
            'post_title' => $this->stringOption('blogger_post_title', '{post_title}'),
            'custom_labels' => [],
            'send_categories' => $this->boolOption('blogger_send_categories', false),
            'send_tags' => $this->boolOption('blogger_send_tags', false),
            'post_content' => $this->stringOption(
                'blogger_post_content',
                "<img src='{post_featured_image_url}'>\n\n{post_content}\n\n<a href='{post_url}'>{post_url}</a>"
            ),
        ];
    }

    private function stringOption(string $optionName, string $default = ''): string
    {
        $value = DB::connection('condo')
            ->table('options')
            ->where('option_name', 'fsp_' . $optionName)
            ->value('option_value');

        return $value === null ? $default : (string) $value;
    }

    private function boolOption(string $optionName, bool $default = false): bool
    {
        $value = $this->stringOption($optionName, $default ? '1' : '0');

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function resolveWordpressUserId(string $username): int
    {
        $authorId = DB::connection('condo')
            ->table('users')
            ->where('user_login', $username)
            ->value('ID');

        return $authorId ? (int) $authorId : 1;
    }

    private function upsertMeta(int $postId, string $metaKey, string $metaValue): void
    {
        $existingMetaId = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $metaKey)
            ->value('meta_id');

        if ($existingMetaId) {
            DB::connection('condo')
                ->table('postmeta')
                ->where('meta_id', $existingMetaId)
                ->update(['meta_value' => $metaValue]);

            DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', $metaKey)
                ->where('meta_id', '!=', $existingMetaId)
                ->delete();

            return;
        }

        DB::connection('condo')->table('postmeta')->insert([
            'post_id' => $postId,
            'meta_key' => $metaKey,
            'meta_value' => $metaValue,
        ]);
    }

    private function deleteMeta(int $postId, string $metaKey): void
    {
        DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $metaKey)
            ->delete();
    }
}
