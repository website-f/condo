<?php

namespace App\Support;

use App\Models\Agent;
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
        $listings = $this->ownedListings($username)
            ->sortByDesc(fn (CondoListing $listing) => (string) $listing->updateddate)
            ->values();
        $currentGroupIds = $this->currentScheduleGroupIdsForPosts(
            $listings->map(fn (CondoListing $listing) => (int) $listing->getKey())->all()
        );

        return $listings->map(fn (CondoListing $listing) => [
                'id' => (int) $listing->getKey(),
                'title' => (string) $listing->propertyname,
                'formatted_price' => (string) $listing->formatted_price,
                'source_key' => 'condo',
                'image_url' => $listing->image_url,
                'current_group_id' => $currentGroupIds[(int) $listing->getKey()] ?? null,
                'has_active_schedule' => isset($currentGroupIds[(int) $listing->getKey()]),
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
        $wordpressUserIds = $this->wordpressUserIdsForAgent($username);
        $rows = collect(DB::connection('condo')
            ->table('fsp_schedules')
            ->join('posts', 'posts.ID', '=', 'fsp_schedules.wp_post_id')
            ->leftJoin('postmeta as agent_username_meta', function ($join) {
                $join->on('agent_username_meta.post_id', '=', 'posts.ID')
                    ->where('agent_username_meta.meta_key', '=', CondoWordpressBridge::META_USERNAME);
            })
            ->join('fsp_channels', 'fsp_channels.id', '=', 'fsp_schedules.channel_id')
            ->join('fsp_channel_sessions', 'fsp_channel_sessions.id', '=', 'fsp_channels.channel_session_id')
            ->leftJoin('postmeta as schedule_group_meta', function ($join) {
                $join->on('schedule_group_meta.post_id', '=', 'posts.ID')
                    ->where('schedule_group_meta.meta_key', '=', 'fsp_schedule_group_id');
            })
            ->leftJoin('postmeta as auto_share_meta', function ($join) {
                $join->on('auto_share_meta.post_id', '=', 'posts.ID')
                    ->where('auto_share_meta.meta_key', '=', 'fsp_enable_auto_share');
            })
            ->leftJoin('postmeta as manual_meta', function ($join) {
                $join->on('manual_meta.post_id', '=', 'posts.ID')
                    ->where('manual_meta.meta_key', '=', 'fsp_schedule_created_manually');
            })
            ->leftJoin('postmeta as cached_meta', function ($join) {
                $join->on('cached_meta.post_id', '=', 'posts.ID')
                    ->where('cached_meta.meta_key', '=', 'fsp_cache_schedules_data');
            })
            ->whereIn('posts.post_type', ['properties', 'post'])
            ->where(function ($query) use ($username, $wordpressUserIds) {
                $query->where('agent_username_meta.meta_value', $username);

                if ($wordpressUserIds !== []) {
                    $query->orWhereIn('posts.post_author', $wordpressUserIds);
                }
            })
            ->orderByDesc('fsp_schedules.send_time')
            ->orderByDesc('fsp_schedules.id')
            ->get([
                'fsp_schedules.id',
                'fsp_schedules.group_id',
                'fsp_schedules.wp_post_id',
                'fsp_schedules.channel_id',
                'fsp_schedules.user_id',
                'fsp_schedules.status',
                'fsp_schedules.error_msg',
                'fsp_schedules.send_time',
                'fsp_schedules.data',
                'fsp_schedules.customization_data',
                'posts.post_title',
                'posts.post_status',
                'posts.post_type',
                'posts.guid',
                'fsp_channels.name',
                'fsp_channels.picture',
                'fsp_channels.channel_type',
                'fsp_channel_sessions.social_network',
                'schedule_group_meta.meta_value as linked_group_id',
                'auto_share_meta.meta_value as auto_share_flag',
                'manual_meta.meta_value as manual_flag',
                'cached_meta.meta_value as cached_schedules',
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
     *     message_preview:string,
     *     has_mixed_messages:bool,
     *     auto_share_enabled:bool,
     *     schedule_created_manually:bool,
     *     is_primary_group:bool,
     *     has_cached_schedule_data:bool,
     *     channel_ids:array<int, int>,
     *     social_networks:array<int, string>,
     *     channels:array<int, array{id:int,name:string,social_network:string,channel_type:string,picture:string}>,
     *     channel_customizations:array<int, array<string, mixed>>,
     *     error_messages:array<int, string>,
     *     is_mutable:bool,
     *     total_channels:int
     * }|null
     */
    public function currentScheduleGroupForListing(string $username, int $listingId): ?array
    {
        $currentGroupId = $this->currentScheduleGroupIdForListing($listingId);

        if ($currentGroupId === null) {
            return null;
        }

        $group = $this->scheduleGroupsForAgent($username)->firstWhere('group_id', $currentGroupId);

        return is_array($group) ? $group : null;
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
        $postId = (int) $listing->getKey();
        $groupId = $this->resolveScheduleGroupId($username, $postId, $groupId);
        $scheduledAt = Carbon::parse((string) $validated['scheduled_at'], config('app.timezone'));
        $channelIds = collect($validated['channel_ids'] ?? [])->map(fn (mixed $id) => (int) $id)->unique()->values();
        $channels = $this->availableChannels()->whereIn('id', $channelIds)->values();
        $existingRows = $this->scheduleRowsForGroup($groupId);
        $existingRowsByChannel = $existingRows->keyBy(fn (object $row) => (int) $row->channel_id);
        $previousPostIds = $existingRows
            ->pluck('wp_post_id')
            ->map(fn (mixed $id) => (int) $id)
            ->unique()
            ->values();
        $messageOverride = trim((string) ($validated['message'] ?? ''));
        $explicitCustomizations = collect((array) ($validated['channel_customizations'] ?? []))
            ->mapWithKeys(function (mixed $value, mixed $key) {
                return is_numeric($key) && is_array($value)
                    ? [(int) $key => $value]
                    : [];
            })
            ->all();

        if ($channels->count() !== $channelIds->count()) {
            throw ValidationException::withMessages([
                'channel_ids' => 'Choose only active FS Poster channels.',
            ]);
        }

        $userId = $this->scheduleUserId($existingRows, $username);
        $sendTimes = $this->buildSendTimes($scheduledAt, $channels->count());
        $scheduleStatus = $this->scheduleStatusForPostStatus((string) ($listing->post_status ?? 'publish'));

        DB::connection('condo')->transaction(function () use (
            $groupId,
            $sendTimes,
            $channels,
            $messageOverride,
            $postId,
            $userId,
            $previousPostIds,
            $existingRowsByChannel,
            $explicitCustomizations,
            $scheduleStatus
        ) {
            DB::connection('condo')
                ->table('fsp_schedules')
                ->where('group_id', $groupId)
                ->delete();

            foreach ($channels->values() as $index => $channel) {
                $existingRow = $existingRowsByChannel->get($channel['id']);
                $customization = $explicitCustomizations[$channel['id']]
                    ?? $this->decodeCustomizationData($existingRow?->customization_data);

                if ($customization === []) {
                    $customization = $this->customizationDataForChannel($channel, '');
                }

                if ($messageOverride !== '') {
                    $customization['post_content'] = $messageOverride;
                }

                DB::connection('condo')->table('fsp_schedules')->insert([
                    'blog_id' => 1,
                    'wp_post_id' => $postId,
                    'user_id' => $existingRow?->user_id !== null ? (int) $existingRow->user_id : $userId,
                    'channel_id' => $channel['id'],
                    'status' => $scheduleStatus,
                    'error_msg' => null,
                    'send_time' => $sendTimes[$index],
                    'remote_post_id' => null,
                    'visit_count' => 0,
                    'planner_id' => $existingRow?->planner_id ?: 0,
                    'data' => $this->normalizeStoredJson($existingRow?->data),
                    'customization_data' => json_encode($customization, JSON_UNESCAPED_SLASHES),
                    'group_id' => $groupId,
                    'created_at' => $existingRow?->created_at ?? now(),
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
                    $this->deleteMeta($previousPostId, 'fsp_cache_schedules_data');
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
                    $this->deleteMeta($postId, 'fsp_cache_schedules_data');
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
        $sortedRows = $rows
            ->sortBy(fn (object $row) => strtotime((string) $row->send_time) ?: 0)
            ->values();
        $first = $sortedRows->first();
        $scheduledAt = Carbon::parse((string) $sortedRows->min('send_time'), config('app.timezone'));
        $statuses = $rows->pluck('status')->map(fn (mixed $status) => (string) $status)->unique()->values()->all();
        $status = $this->displayStatus($statuses);
        $channelCustomizations = $sortedRows
            ->mapWithKeys(fn (object $row) => [
                (int) $row->channel_id => $this->decodeCustomizationData($row->customization_data),
            ])
            ->all();
        $messages = collect($channelCustomizations)
            ->map(fn (array $customization) => trim((string) ($customization['post_content'] ?? '')))
            ->filter(fn (string $value) => $value !== '')
            ->unique()
            ->values();
        $hasMixedMessages = $messages->count() > 1;
        $message = $hasMixedMessages ? '' : (string) ($messages->first() ?? '');
        $messagePreview = $message !== '' ? $message : ($hasMixedMessages
            ? 'Channel-specific content is configured in WordPress FS Poster for this schedule group.'
            : '');
        $linkedGroupId = trim((string) ($first->linked_group_id ?? ''));
        $autoShareEnabled = $this->metaFlag($first->auto_share_flag ?? null, false);
        $scheduleCreatedManually = $this->metaFlag($first->manual_flag ?? null, false);
        $hasCachedScheduleData = trim((string) ($first->cached_schedules ?? '')) !== '';
        $contentType = (string) ($first->post_type ?? 'post');
        $canManageInLaravel = $contentType === 'properties';
        $viewUrl = $contentType === 'properties'
            ? route('listings.show', [
                'id' => (int) $first->wp_post_id,
                'source' => 'condo',
                'return_source' => 'condo',
            ])
            : route('news.show', (int) $first->wp_post_id);

        return [
            'group_id' => (string) $first->group_id,
            'listing_id' => (int) $first->wp_post_id,
            'listing_title' => trim((string) $first->post_title) !== '' ? trim((string) $first->post_title) : 'Untitled condo listing',
            'listing_url' => trim((string) $first->guid),
            'content_type' => $contentType,
            'content_type_label' => $contentType === 'properties' ? 'Listing' : 'News',
            'view_url' => $viewUrl,
            'can_manage_in_laravel' => $canManageInLaravel,
            'scheduled_at' => $scheduledAt,
            'scheduled_at_form' => $scheduledAt->format('Y-m-d\TH:i'),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_color' => $this->statusColor($status),
            'message' => trim((string) $message),
            'message_preview' => trim((string) $messagePreview),
            'has_mixed_messages' => $hasMixedMessages,
            'auto_share_enabled' => $autoShareEnabled,
            'schedule_created_manually' => $scheduleCreatedManually,
            'is_primary_group' => $linkedGroupId !== '' && $linkedGroupId === (string) $first->group_id,
            'has_cached_schedule_data' => $hasCachedScheduleData,
            'channel_ids' => $rows->pluck('channel_id')->map(fn (mixed $id) => (int) $id)->unique()->values()->all(),
            'social_networks' => $rows->pluck('social_network')->map(fn (mixed $value) => (string) $value)->unique()->values()->all(),
            'channels' => $rows->map(fn (object $row) => [
                'id' => (int) $row->channel_id,
                'name' => trim((string) $row->name) !== '' ? trim((string) $row->name) : '[no name]',
                'social_network' => (string) $row->social_network,
                'channel_type' => (string) $row->channel_type,
                'picture' => (string) $row->picture,
            ])->unique('id')->values()->all(),
            'channel_customizations' => $channelCustomizations,
            'error_messages' => $rows->pluck('error_msg')->map(fn (mixed $value) => trim((string) $value))->filter()->unique()->values()->all(),
            'is_mutable' => $canManageInLaravel && $sortedRows->every(
                fn (object $row) => in_array((string) $row->status, ['not_sent', 'draft'], true)
                    && Carbon::parse((string) $row->send_time, config('app.timezone'))->isFuture()
            ),
            'total_channels' => $rows->pluck('channel_id')->unique()->count(),
        ];
    }

    /**
     * @return Collection<int, CondoListing>
     */
    private function ownedListings(string $username): Collection
    {
        $wordpressUserIds = $this->wordpressUserIdsForAgent($username);

        return CondoListing::query()
            ->active()
            ->with('details')
            ->get()
            ->filter(fn (CondoListing $listing) => $listing->username === $username
                || in_array((int) ($listing->getAttribute('post_author') ?? 0), $wordpressUserIds, true))
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

    /**
     * @param  array<int, int>  $postIds
     * @return array<int, string>
     */
    private function currentScheduleGroupIdsForPosts(array $postIds): array
    {
        $postIds = array_values(array_unique(array_filter(array_map('intval', $postIds), fn (int $id) => $id > 0)));

        if ($postIds === []) {
            return [];
        }

        return DB::connection('condo')
            ->table('postmeta')
            ->where('meta_key', 'fsp_schedule_group_id')
            ->whereIn('post_id', $postIds)
            ->pluck('meta_value', 'post_id')
            ->filter(fn (mixed $value) => is_string($value) && trim($value) !== '')
            ->map(fn (mixed $value) => trim((string) $value))
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function wordpressUserIdsForAgent(string $username): array
    {
        $agent = Agent::query()->with('detail')->where('username', $username)->first();
        $email = trim(strtolower((string) ($agent?->detail?->email ?? '')));

        return DB::connection('condo')
            ->table('users')
            ->where(function ($query) use ($username, $email) {
                $query->where('user_login', $username);

                if ($email !== '') {
                    $query->orWhereRaw('LOWER(user_email) = ?', [$email]);
                }
            })
            ->pluck('ID')
            ->map(fn (mixed $value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function currentScheduleGroupIdForListing(int $postId): ?string
    {
        $groupId = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', 'fsp_schedule_group_id')
            ->value('meta_value');

        return is_string($groupId) && trim($groupId) !== '' ? trim($groupId) : null;
    }

    private function resolveScheduleGroupId(string $username, int $postId, ?string $requestedGroupId = null): string
    {
        $requestedGroupId = is_string($requestedGroupId) && trim($requestedGroupId) !== ''
            ? trim($requestedGroupId)
            : null;

        if ($requestedGroupId !== null) {
            return $requestedGroupId;
        }

        $currentGroupId = $this->currentScheduleGroupIdForListing($postId);

        if ($currentGroupId === null) {
            return (string) Str::uuid();
        }

        $currentGroup = $this->scheduleGroupsForAgent($username)->firstWhere('group_id', $currentGroupId);

        if (is_array($currentGroup) && ($currentGroup['is_mutable'] ?? false)) {
            return $currentGroupId;
        }

        return (string) Str::uuid();
    }

    /**
     * @return Collection<int, object>
     */
    private function scheduleRowsForGroup(string $groupId): Collection
    {
        return collect(DB::connection('condo')
            ->table('fsp_schedules')
            ->where('group_id', $groupId)
            ->orderBy('send_time')
            ->orderBy('id')
            ->get([
                'id',
                'group_id',
                'wp_post_id',
                'channel_id',
                'user_id',
                'status',
                'send_time',
                'planner_id',
                'data',
                'customization_data',
                'created_at',
            ]));
    }

    private function scheduleUserId(Collection $existingRows, string $username): int
    {
        $existingUserId = $existingRows
            ->pluck('user_id')
            ->map(fn (mixed $value) => is_numeric($value) ? (int) $value : null)
            ->first(fn (?int $value) => $value !== null);

        if ($existingUserId !== null) {
            return $existingUserId;
        }

        return $this->resolveWordpressUserId($username);
    }

    /**
     * @return array<int, string>
     */
    private function buildSendTimes(Carbon $scheduledAt, int $count): array
    {
        $sendTimes = [];
        $intervalSeconds = $this->postIntervalSeconds();

        for ($index = 0; $index < $count; $index++) {
            $sendTimes[] = $scheduledAt->copy()->addSeconds($intervalSeconds * $index)->format('Y-m-d H:i:s');
        }

        return $sendTimes;
    }

    private function postIntervalSeconds(): int
    {
        return $this->boolOption('enable_post_interval', false)
            ? max(0, (int) $this->stringOption('post_interval', '0'))
            : 0;
    }

    private function scheduleStatusForPostStatus(string $postStatus): string
    {
        return match ($postStatus) {
            'trash' => 'trash',
            'auto-draft' => 'auto-draft',
            'draft', 'pending' => 'draft',
            default => 'not_sent',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeCustomizationData(mixed $customizationData): array
    {
        if (! is_string($customizationData) || trim($customizationData) === '') {
            return [];
        }

        $decoded = json_decode($customizationData, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeStoredJson(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return json_encode([], JSON_UNESCAPED_SLASHES);
    }

    private function metaFlag(mixed $value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
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
