<?php

namespace App\Http\Controllers;

use App\Models\CondoListing;
use App\Support\FsPosterBridge;
use App\Support\RecentlyDeletedService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SocialMediaController extends Controller
{
    public function __construct(
        private readonly FsPosterBridge $fsPosterBridge,
        private readonly RecentlyDeletedService $recentlyDeletedService,
    )
    {
    }

    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $channels = $this->fsPosterBridge->availableChannels();
        $schedules = $this->fsPosterBridge->scheduleGroupsForAgent($username);
        $listings = $this->fsPosterBridge->availableListings($username);
        $viewMode = in_array($request->query('view'), ['calendar', 'table'], true)
            ? (string) $request->query('view')
            : 'calendar';

        $statusFilter = trim((string) $request->query('status', ''));
        $networkFilter = trim((string) $request->query('network', ''));
        $search = trim((string) $request->query('search', ''));

        $filtered = $schedules
            ->filter(function (array $schedule) use ($statusFilter, $networkFilter, $search) {
                if ($statusFilter !== '' && $schedule['status'] !== $statusFilter) {
                    return false;
                }

                if ($networkFilter !== '' && ! in_array($networkFilter, $schedule['social_networks'], true)) {
                    return false;
                }

                if ($search !== '') {
                    $needle = mb_strtolower($search);

                    return str_contains(mb_strtolower($schedule['listing_title']), $needle)
                        || str_contains(mb_strtolower($schedule['message']), $needle);
                }

                return true;
            })
            ->values();

        $posts = $this->paginateCollection($filtered, $request, 10);
        $calendarEvents = $filtered
            ->map(function (array $schedule) {
                $start = $schedule['scheduled_at']->copy();
                $end = $start->copy()->addMinutes(45);
                $messagePreview = $schedule['message_preview'] !== ''
                    ? $schedule['message_preview']
                    : 'Using the WordPress FS Poster template for this channel set.';

                return [
                    'id' => $schedule['group_id'],
                    'title' => $schedule['listing_title'],
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                    'backgroundColor' => $schedule['status_color'],
                    'borderColor' => $schedule['status_color'],
                    'classNames' => ['schedule-status-' . $schedule['status']],
                    'extendedProps' => [
                        'group_id' => $schedule['group_id'],
                        'status' => $schedule['status'],
                        'status_label' => $schedule['status_label'],
                        'content_type_label' => $schedule['content_type_label'],
                        'view_url' => $schedule['view_url'],
                        'edit_url' => $schedule['can_manage_in_laravel'] && $schedule['is_mutable']
                            ? route('social.edit', $schedule['group_id'])
                            : null,
                        'can_manage_in_laravel' => $schedule['can_manage_in_laravel'],
                        'is_mutable' => $schedule['is_mutable'],
                        'networks' => collect($schedule['social_networks'])->map(fn (string $network) => strtoupper($network))->values()->all(),
                        'channels' => $schedule['total_channels'],
                        'channel_names' => collect($schedule['channels'])->pluck('name')->values()->all(),
                        'message' => Str::limit($messagePreview, 160),
                        'message_full' => $messagePreview,
                        'error_messages' => $schedule['error_messages'],
                        'time_label' => $schedule['scheduled_at']->format('D, d M Y h:i A'),
                        'action_label' => $schedule['is_mutable'] ? 'Edit Schedule' : 'View ' . $schedule['content_type_label'],
                    ],
                ];
            })
            ->values();

        $upcomingSchedules = $filtered
            ->filter(fn (array $schedule) => $schedule['scheduled_at']->isFuture())
            ->sortBy(fn (array $schedule) => $schedule['scheduled_at']->timestamp)
            ->take(6)
            ->values();

        $networkSummary = $channels
            ->groupBy('social_network')
            ->map(function (Collection $group, string $network) {
                return [
                    'key' => $network,
                    'label' => match ($network) {
                        'google_b' => 'Google Business',
                        'fb' => 'Facebook',
                        default => Str::headline(str_replace('_', ' ', $network)),
                    },
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $stats = [
            'channels' => $channels->count(),
            'scheduled' => $schedules->where('status', 'scheduled')->count(),
            'sent' => $schedules->where('status', 'success')->count(),
            'issues' => $schedules->whereIn('status', ['error', 'mixed'])->count(),
        ];

        return view('social.index', compact(
            'channels',
            'posts',
            'stats',
            'calendarEvents',
            'listings',
            'viewMode',
            'upcomingSchedules',
            'networkSummary',
            'statusFilter',
            'networkFilter',
            'search'
        ));
    }

    public function create(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $channels = $this->fsPosterBridge->availableChannels();
        $listings = $this->fsPosterBridge->availableListings($username);
        $selectedListingId = (int) $request->query('listing', 0);
        $selectedListingId = $listings->firstWhere('id', $selectedListingId)['id'] ?? ($listings->first()['id'] ?? 0);
        $selectedListing = $listings->firstWhere('id', $selectedListingId);

        if ($request->filled('listing') && is_array($selectedListing) && ! empty($selectedListing['current_group_id'])) {
            return redirect()
                ->route('social.edit', $selectedListing['current_group_id'])
                ->with('success', 'This condo listing already has a live FS Poster schedule. Editing it here keeps Laravel and WordPress in sync.');
        }

        $defaultScheduledAt = now()->addMinutes(15)->format('Y-m-d\TH:i');

        return view('social.form', [
            'pageTitle' => 'Create Social Schedule',
            'formAction' => route('social.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Queue Schedule',
            'channels' => $channels,
            'listings' => $listings,
            'schedule' => [
                'listing_id' => old('listing_id', $selectedListingId),
                'channel_ids' => old('channel_ids', []),
                'scheduled_at_form' => old('scheduled_at', $defaultScheduledAt),
                'message' => old('message', ''),
            ],
            'existingGroup' => null,
        ]);
    }

    public function store(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $validated = $this->validateSchedule($request);
        $listing = $this->findOwnedCondoListingOrFail((int) $validated['listing_id'], $username);
        $groupId = $this->fsPosterBridge->storeScheduleGroup($listing, $username, $validated);

        return redirect()
            ->route('social.edit', $groupId)
            ->with('success', 'FS Poster schedule queued for this condo listing.');
    }

    public function edit(string $groupId)
    {
        $username = Auth::guard('agent')->user()->username;
        $group = $this->fsPosterBridge->findScheduleGroupForAgent($username, $groupId);

        if (! $group['can_manage_in_laravel']) {
            abort(403);
        }

        $channels = $this->fsPosterBridge->availableChannels();
        $listings = $this->fsPosterBridge->availableListings($username);

        return view('social.form', [
            'pageTitle' => 'Edit Social Schedule',
            'formAction' => route('social.update', $groupId),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update Schedule',
            'channels' => $channels,
            'listings' => $listings,
            'schedule' => [
                'listing_id' => old('listing_id', $group['listing_id']),
                'channel_ids' => old('channel_ids', $group['channel_ids']),
                'scheduled_at_form' => old('scheduled_at', $group['scheduled_at_form']),
                'message' => old('message', $group['message']),
            ],
            'existingGroup' => $group,
        ]);
    }

    public function update(Request $request, string $groupId)
    {
        $username = Auth::guard('agent')->user()->username;
        $group = $this->fsPosterBridge->findScheduleGroupForAgent($username, $groupId);

        if (! $group['can_manage_in_laravel']) {
            abort(403);
        }

        if (! $group['is_mutable']) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Only future queued schedules can be edited here. Past or completed schedules stay read-only.',
            ]);
        }

        $validated = $this->validateSchedule($request);
        $listing = $this->findOwnedCondoListingOrFail((int) $validated['listing_id'], $username);
        $this->fsPosterBridge->storeScheduleGroup($listing, $username, $validated, $groupId);

        return redirect()
            ->route('social.edit', $groupId)
            ->with('success', 'FS Poster schedule updated.');
    }

    public function destroy(string $groupId)
    {
        $username = Auth::guard('agent')->user()->username;

        if (! $this->recentlyDeletedService->registryAvailable()) {
            return redirect()
                ->route('social.index')
                ->withErrors([
                    'type' => 'Run php artisan migrate first. FS Poster schedule recovery needs the shared cms_deleted_items table.',
                ]);
        }

        $group = $this->fsPosterBridge->findScheduleGroupForAgent($username, $groupId);

        if (! $group['can_manage_in_laravel']) {
            abort(403);
        }

        $this->recentlyDeletedService->rememberSocialSchedule($username, $group);
        $this->fsPosterBridge->deleteScheduleGroup($username, $groupId);

        return redirect()
            ->route('social.index')
            ->with('success', 'FS Poster schedule moved to Recently Deleted.');
    }

    private function findOwnedCondoListingOrFail(int $listingId, string $username): CondoListing
    {
        $listing = CondoListing::query()
            ->active()
            ->with('details')
            ->findOrFail($listingId);

        if ($listing->username !== $username) {
            abort(403);
        }

        return $listing;
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'listing_id' => ['required', 'integer'],
            'channel_ids' => ['required', 'array', 'min:1'],
            'channel_ids.*' => ['integer'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:4000'],
        ]);
    }

    private function paginateCollection(Collection $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $currentPage = max(1, (int) $request->query('page', 1));
        $currentItems = $items->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }
}
