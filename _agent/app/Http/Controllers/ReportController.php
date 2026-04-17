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
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ReportController extends Controller
{
    public function __construct(
        private readonly CondoPackageManager $condoPackageManager,
        private readonly FsPosterBridge $fsPosterBridge,
    ) {
    }

    public function index(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        $agent->load('subscription');
        $username = $agent->username;
        $period = $this->resolvePeriod($request->get('period', '30'));
        $cutoff = now()->subDays($period)->startOfDay();

        $listingItems = $this->reportListings($agent)
            ->filter(fn (array $listing) => $this->withinPeriod($listing['activity_at'] ?? null, $cutoff))
            ->values();
        $totalListings = $listingItems->count();
        $listingsByType = $this->aggregateFacet($listingItems, 'listingtype');
        $listingsByPropertyType = $this->aggregateFacet($listingItems, 'propertytype');
        $listingsByState = $this->aggregateFacet($listingItems, 'state', 10);

        $socialSchedules = $this->reportSocialSchedules($agent)
            ->filter(fn (array $schedule) => $this->withinPeriod($schedule['scheduled_at'] ?? null, $cutoff))
            ->values();
        $totalSocialPosts = $socialSchedules->count();
        $socialByPlatform = $socialSchedules
            ->flatMap(fn (array $schedule) => collect($schedule['social_networks'] ?? [])->unique()->values())
            ->map(fn (mixed $network) => trim(strtolower((string) $network)))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->map(fn (int $total, string $platform) => (object) [
                'platform' => $platform,
                'total' => $total,
            ])
            ->values();

        $articleItems = ManagedArticle::query()
            ->manageable()
            ->ownedByAgent($username)
            ->get()
            ->filter(fn (ManagedArticle $article) => $this->withinPeriod($this->articleActivityAt($article), $cutoff))
            ->values();
        $totalArticles = $articleItems->count();
        $publishedArticles = $articleItems->where('post_status', 'publish')->count();
        $scheduledArticles = $articleItems->where('post_status', 'future')->count();
        $draftArticles = $articleItems->where('post_status', 'draft')->count();
        $articlesByStatus = $articleItems
            ->groupBy(fn (ManagedArticle $article) => (string) $article->post_status)
            ->map(fn (Collection $group, string $status) => (object) [
                'post_status' => $status,
                'total' => $group->count(),
            ])
            ->sortBy('post_status')
            ->values();
        $recentArticles = $articleItems
            ->sortByDesc(fn (ManagedArticle $article) => $this->articleActivityAt($article)?->timestamp ?? 0)
            ->take(5)
            ->values();

        return view('reports.index', compact(
            'totalListings', 'listingsByType', 'listingsByPropertyType', 'listingsByState',
            'totalSocialPosts', 'socialByPlatform',
            'totalArticles', 'publishedArticles', 'scheduledArticles', 'draftArticles', 'articlesByStatus', 'recentArticles',
            'period'
        ));
    }

    private function resolvePeriod(mixed $value): int
    {
        $period = (int) $value;

        return in_array($period, [7, 30, 90, 365], true) ? $period : 30;
    }

    /**
     * @return Collection<int, array{listingtype:string,propertytype:string,state:string,activity_at:?Carbon}>
     */
    private function reportListings(Agent $agent): Collection
    {
        $username = $agent->username;
        $items = collect();

        try {
            $items = $items->concat(
                Listing::query()
                    ->active()
                    ->where('username', $username)
                    ->get()
                    ->map(fn (Listing $listing) => [
                        'listingtype' => $this->normalizeFacet($listing->listingtype),
                        'propertytype' => $this->normalizeFacet($listing->propertytype),
                        'state' => $this->normalizeFacet($listing->state),
                        'activity_at' => $this->legacyTimestamp($listing->createddate ?? null, $listing->updateddate ?? null),
                    ])
            );
        } catch (Throwable) {
            // Skip unavailable listing sources in environments without the legacy schema.
        }

        try {
            $items = $items->concat(
                IcpListing::query()
                    ->active()
                    ->where('username', $username)
                    ->get()
                    ->map(fn (IcpListing $listing) => [
                        'listingtype' => $this->normalizeFacet($listing->listingtype),
                        'propertytype' => $this->normalizeFacet($listing->propertytype),
                        'state' => $this->normalizeFacet($listing->state),
                        'activity_at' => $this->legacyTimestamp($listing->createddate ?? null, $listing->updateddate ?? null),
                    ])
            );
        } catch (Throwable) {
            // ICP is optional, so missing tables should not break reports.
        }

        if ($this->condoPackageManager->hasAccess($agent)) {
            try {
                $items = $items->concat(
                    CondoListing::query()
                        ->active()
                        ->with('details')
                        ->get()
                        ->filter(fn (CondoListing $listing) => $listing->username === $username)
                        ->map(fn (CondoListing $listing) => [
                            'listingtype' => $this->normalizeFacet($listing->listingtype),
                            'propertytype' => $this->normalizeFacet($listing->propertytype),
                            'state' => $this->normalizeFacet($listing->state),
                            'activity_at' => $this->legacyTimestamp($listing->createddate ?? null, $listing->updateddate ?? null),
                        ])
                );
            } catch (Throwable) {
                // Keep reports usable even if WordPress is temporarily unavailable.
            }
        }

        return $items->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function reportSocialSchedules(Agent $agent): Collection
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

    private function aggregateFacet(Collection $items, string $key, ?int $limit = null): Collection
    {
        $aggregated = $items
            ->groupBy(fn (array $item) => $this->normalizeFacet($item[$key] ?? ''))
            ->map(fn (Collection $group, string $value) => (object) [
                $key => $value,
                'total' => $group->count(),
            ])
            ->sortByDesc('total')
            ->values();

        if ($limit !== null) {
            return $aggregated->take($limit)->values();
        }

        return $aggregated;
    }

    private function normalizeFacet(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : 'Unspecified';
    }

    private function articleActivityAt(ManagedArticle $article): ?Carbon
    {
        $modifiedAt = trim((string) ($article->post_modified ?? ''));

        if ($modifiedAt !== '') {
            try {
                return Carbon::parse($modifiedAt, config('app.timezone'));
            } catch (Throwable) {
                // Fall back to the publish date if the modified timestamp is malformed.
            }
        }

        return $article->publishedAt();
    }

    private function withinPeriod(?Carbon $timestamp, Carbon $cutoff): bool
    {
        return ! $timestamp || $timestamp->gte($cutoff);
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
