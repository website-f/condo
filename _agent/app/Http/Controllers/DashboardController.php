<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\CondoListing;
use App\Models\IcpListing;
use App\Models\Listing;
use App\Models\ManagedArticle;
use App\Support\CondoPackageManager;
use App\Support\FsPosterBridge;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CondoPackageManager $condoPackageManager,
        private readonly FsPosterBridge $fsPosterBridge,
    ) {
    }

    public function index()
    {
        $agent = Auth::guard('agent')->user();
        $agent->load('subscription');
        $username = $agent->username;

        $allListings = $this->dashboardListings($agent);
        $totalListings = $allListings->count();
        $totalArticles = ManagedArticle::query()->manageable()->ownedByAgent($username)->count();
        $publishedArticles = ManagedArticle::query()->manageable()->ownedByAgent($username)->where('post_status', 'publish')->count();
        $scheduledArticles = ManagedArticle::query()->manageable()->ownedByAgent($username)->where('post_status', 'future')->count();
        $socialSchedules = $this->dashboardSocialSchedules($agent);
        $totalSocialSchedules = $socialSchedules->count();
        $scheduledPosts = $socialSchedules->where('status', 'scheduled')->count();
        $publishedPosts = $socialSchedules->where('status', 'success')->count();

        $recentListings = $allListings
            ->sortByDesc(fn ($listing) => $this->legacyTimestamp($listing->createddate ?? null, $listing->updateddate ?? null)?->timestamp ?? 0)
            ->take(5)
            ->values();

        $recentArticles = ManagedArticle::query()
            ->manageable()
            ->ownedByAgent($username)
            ->orderByDesc('post_modified')
            ->take(5)
            ->get();

        $upcomingPosts = $socialSchedules
            ->filter(fn (array $schedule) => $schedule['scheduled_at']->isFuture())
            ->sortBy(fn (array $schedule) => $schedule['scheduled_at']->timestamp)
            ->take(5)
            ->map(fn (array $schedule) => [
                'network_label' => collect($schedule['social_networks'])
                    ->map(fn (string $network) => strtoupper($network))
                    ->implode(', '),
                'message_preview' => $schedule['message_preview'] !== ''
                    ? $schedule['message_preview']
                    : 'Using saved template.',
                'scheduled_at' => $schedule['scheduled_at'],
            ])
            ->values();

        return view('dashboard', compact(
            'agent',
            'totalListings',
            'totalArticles',
            'publishedArticles',
            'scheduledArticles',
            'totalSocialSchedules',
            'scheduledPosts',
            'publishedPosts',
            'recentListings',
            'recentArticles',
            'upcomingPosts'
        ));
    }

    /**
     * @return Collection<int, Listing|IcpListing|CondoListing>
     */
    private function dashboardListings(Agent $agent): Collection
    {
        $username = $agent->username;
        $listings = collect();

        try {
            $listings = $listings->concat(
                Listing::query()
                    ->active()
                    ->where('username', $username)
                    ->get()
                    ->each(fn (Listing $listing) => $listing->setAttribute('dashboard_source_label', 'IPP'))
            );
        } catch (Throwable) {
            // Keep the dashboard usable even if the legacy listing source is unavailable.
        }

        try {
            $listings = $listings->concat(
                IcpListing::query()
                    ->active()
                    ->where('username', $username)
                    ->get()
                    ->each(fn (IcpListing $listing) => $listing->setAttribute('dashboard_source_label', 'ICP'))
            );
        } catch (Throwable) {
            // ICP is optional in some environments.
        }

        if ($this->condoPackageManager->hasAccess($agent)) {
            try {
                $listings = $listings->concat(
                    CondoListing::query()
                        ->active()
                        ->with('details')
                        ->get()
                        ->filter(fn (CondoListing $listing) => $listing->username === $username)
                        ->each(fn (CondoListing $listing) => $listing->setAttribute('dashboard_source_label', 'Condo'))
                );
            } catch (Throwable) {
                // Condo listings should not break the rest of the dashboard if WordPress is unavailable.
            }
        }

        return $listings->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function dashboardSocialSchedules(Agent $agent): Collection
    {
        if (! $this->condoPackageManager->hasAccess($agent)) {
            return collect();
        }

        try {
            return $this->fsPosterBridge->scheduleDisplayGroupsForAgent($agent->username)->values();
        } catch (Throwable) {
            return collect();
        }
    }

    private function legacyTimestamp(mixed $primary, mixed $secondary = null): ?Carbon
    {
        foreach ([$primary, $secondary] as $value) {
            $timestamp = $this->parseLegacyTimestamp($value);

            if ($timestamp instanceof Carbon) {
                return $timestamp;
            }
        }

        return null;
    }

    private function parseLegacyTimestamp(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            if (ctype_digit($value) && strlen($value) === 14) {
                return Carbon::createFromFormat('YmdHis', $value, config('app.timezone'));
            }

            if (ctype_digit($value) && strlen($value) === 10) {
                return Carbon::createFromTimestamp((int) $value, config('app.timezone'));
            }

            if (ctype_digit($value) && strlen($value) === 8) {
                return Carbon::createFromFormat('Ymd', $value, config('app.timezone'))->startOfDay();
            }

            return Carbon::parse($value, config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }
}
