<?php

namespace App\Http\Controllers;

use App\Models\CondoListing;
use App\Support\RankMathBridge;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SeoController extends Controller
{
    public function __construct(private readonly RankMathBridge $rankMathBridge)
    {
    }

    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $search = trim((string) $request->query('search', ''));

        $rows = $this->ownedCondoListings($username)
            ->filter(function (CondoListing $listing) use ($search) {
                if ($search === '') {
                    return true;
                }

                $needle = mb_strtolower($search);

                return str_contains(mb_strtolower((string) $listing->propertyname), $needle)
                    || str_contains(mb_strtolower((string) $listing->area), $needle)
                    || str_contains(mb_strtolower((string) $listing->state), $needle)
                    || str_contains(mb_strtolower((string) $listing->keywords), $needle);
            })
            ->sortByDesc(fn (CondoListing $listing) => (string) $listing->updateddate)
            ->values()
            ->map(fn (CondoListing $listing) => [
                'listing' => $listing,
                'seo' => $this->rankMathBridge->currentSeoData($listing),
            ]);

        $settings = $this->paginateCollection($rows, $request, 10);

        return view('seo.index', compact('settings', 'search'));
    }

    public function edit(int $listingId)
    {
        $listing = $this->findOwnedCondoListingOrFail($listingId);
        $seo = $this->rankMathBridge->currentSeoData($listing);

        return view('seo.edit', compact('listing', 'seo'));
    }

    public function update(Request $request, int $listingId)
    {
        $listing = $this->findOwnedCondoListingOrFail($listingId);

        $validated = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:120'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'twitter_title' => ['nullable', 'string', 'max:120'],
            'twitter_description' => ['nullable', 'string', 'max:320'],
            'robots' => ['nullable', 'array'],
            'robots.*' => ['string', Rule::in(['noindex', 'nofollow', 'noarchive', 'nosnippet', 'noimageindex'])],
        ]);

        $this->rankMathBridge->saveManualSeo($listing, $validated);

        return redirect()
            ->route('seo.edit', $listingId)
            ->with('success', 'Rank Math metadata updated for this condo listing.');
    }

    /**
     * @return Collection<int, CondoListing>
     */
    private function ownedCondoListings(string $username): Collection
    {
        return CondoListing::query()
            ->active()
            ->with('details')
            ->get()
            ->filter(fn (CondoListing $listing) => $listing->username === $username)
            ->values();
    }

    private function findOwnedCondoListingOrFail(int $listingId): CondoListing
    {
        $listing = CondoListing::query()
            ->active()
            ->with('details')
            ->findOrFail($listingId);

        if ($listing->username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }

        return $listing;
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
