<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CondoListing;
use App\Models\IcpListing;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ListingApiController extends Controller
{
    private const SOURCES = ['all', 'ipp', 'icp', 'condo'];

    public function index(Request $request): JsonResponse
    {
        $source = $this->resolveSource($request->query('source'));
        $username = $this->resolveUsernameFilter($request);
        $perPage = max(1, min(50, (int) $request->get('per_page', 12)));

        if ($source === 'all') {
            $listings = collect(['ipp', 'icp'])
                ->filter(fn (string $sourceKey) => $this->sourceAvailable($sourceKey))
                ->flatMap(function (string $sourceKey) use ($request, $username) {
                    $query = $this->queryForSource($sourceKey)->active()->with(['details', 'agent.detail']);
                    $this->applyFilters($query, $request, $username);

                    return $query->get()->map(fn ($listing) => $this->decorateListing($listing, $sourceKey));
                });

            if ($this->sourceAvailable('condo')) {
                $listings = $listings->concat(
                    $this->applyCollectionFilters($this->condoListingsCollection(true), $request, $username)
                        ->map(fn ($listing) => $this->decorateListing($listing, 'condo'))
                );
            }

            $listings = $listings
                ->sortByDesc(fn (Listing $listing) => preg_replace('/\D+/', '', (string) $listing->createddate) ?: '0')
                ->values();

            return response()->json($this->paginateCollection($listings, $request, $perPage));
        }

        if (! $this->sourceAvailable($source)) {
            return response()->json($this->paginateCollection(collect(), $request, $perPage));
        }

        if ($source === 'condo') {
            $listings = $this->applyCollectionFilters($this->condoListingsCollection(true), $request, $username)
                ->sortByDesc(fn (Listing $listing) => preg_replace('/\D+/', '', (string) $listing->createddate) ?: '0')
                ->values()
                ->map(fn ($listing) => $this->decorateListing($listing, 'condo'));

            return response()->json($this->paginateCollection($listings, $request, $perPage));
        }

        $query = $this->queryForSource($source)
            ->active()
            ->with(['details', 'agent.detail']);

        $this->applyFilters($query, $request, $username);

        $paginator = $query
            ->orderBy('createddate', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn ($listing) => $this->decorateListing($listing, $source)
            )
        );

        return response()->json($paginator);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $source = $this->resolveSource($request->query('source'));

        if ($source === 'all') {
            foreach (['ipp', 'icp', 'condo'] as $candidate) {
                if (! $this->sourceAvailable($candidate)) {
                    continue;
                }

                $listing = $this->queryForSource($candidate)
                    ->active()
                    ->with(['details', 'agent.detail'])
                    ->find($id);

                if ($listing) {
                    return response()->json($this->decorateListing($listing, $candidate));
                }
            }

            abort(404);
        }

        if (! $this->sourceAvailable($source)) {
            abort(404);
        }

        $listing = $this->queryForSource($source)
            ->active()
            ->with(['details', 'agent.detail'])
            ->findOrFail($id);

        return response()->json($this->decorateListing($listing, $source));
    }

    private function resolveSource(?string $source): string
    {
        $source = strtolower(trim((string) $source));

        return in_array($source, self::SOURCES, true) ? $source : 'all';
    }

    private function resolveUsernameFilter(Request $request): ?string
    {
        $username = trim((string) $request->query('username', ''));

        if ($username !== '') {
            return $username;
        }

        $subdomain = trim((string) $request->query('subdomain', ''));

        if ($subdomain === '') {
            return null;
        }

        $host = preg_replace('#^https?://#i', '', $subdomain) ?? $subdomain;
        $host = strtolower(trim((string) explode('/', $host, 2)[0]));
        $host = explode(':', $host, 2)[0];

        if ($host === '' || in_array($host, ['condo.com.my', 'www.condo.com.my'], true)) {
            return null;
        }

        $parts = array_values(array_filter(explode('.', $host)));

        return $parts[0] ?? null;
    }

    private function sourceAvailable(string $source): bool
    {
        return match ($source) {
            'ipp' => true,
            'icp' => $this->icpSourceAvailable(),
            'condo' => $this->condoSourceAvailable(),
            default => false,
        };
    }

    private function icpSourceAvailable(): bool
    {
        static $available = null;

        if ($available !== null) {
            return $available;
        }

        $connection = IcpListing::resolvedConnectionName();
        $listingTable = IcpListing::resolvedTableName();
        $detailTable = IcpListing::resolvedDetailTableName();

        try {
            return $available = Schema::connection($connection)->hasTable($listingTable)
                && Schema::connection($connection)->hasTable($detailTable);
        } catch (Throwable) {
            return $available = false;
        }
    }

    private function condoSourceAvailable(): bool
    {
        static $available = null;

        if ($available !== null) {
            return $available;
        }

        return $available = CondoListing::schemaAvailable();
    }

    private function queryForSource(string $source)
    {
        return match ($source) {
            'icp' => IcpListing::query(),
            'condo' => CondoListing::query(),
            default => Listing::query(),
        };
    }

    private function applyFilters($query, Request $request, ?string $username = null): void
    {
        if ($username !== null && $username !== '') {
            $query->where('username', $username);
        }
        if ($request->filled('propertyid')) {
            $query->where('propertyid', $request->propertyid);
        }
        if ($request->filled('listingtype')) {
            $query->where('listingtype', $request->listingtype);
        }
        if ($request->filled('propertytype')) {
            $query->where('propertytype', $request->propertytype);
        }
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('search')) {
            $query->where(function ($nestedQuery) use ($request) {
                $nestedQuery->where('propertyname', 'like', '%' . $request->search . '%')
                    ->orWhere('area', 'like', '%' . $request->search . '%');
            });
        }
    }

    private function condoListingsCollection(bool $withRelations = false): Collection
    {
        $query = CondoListing::query()->active()->with('details');

        if ($withRelations) {
            $query->with(['agent.detail']);
        }

        return $query->get()->values();
    }

    private function applyCollectionFilters(Collection $listings, Request $request, ?string $username = null): Collection
    {
        return $listings
            ->when($username !== null && $username !== '', fn (Collection $items) => $items->filter(
                fn ($listing) => $listing->username === $username
            ))
            ->when($request->filled('propertyid'), fn (Collection $items) => $items->filter(
                fn ($listing) => $listing->propertyid === $request->propertyid
            ))
            ->when($request->filled('listingtype'), fn (Collection $items) => $items->filter(
                fn ($listing) => $listing->listingtype === $request->listingtype
            ))
            ->when($request->filled('propertytype'), fn (Collection $items) => $items->filter(
                fn ($listing) => $listing->propertytype === $request->propertytype
            ))
            ->when($request->filled('state'), fn (Collection $items) => $items->filter(
                fn ($listing) => $listing->state === $request->state
            ))
            ->when($request->filled('min_price'), fn (Collection $items) => $items->filter(
                fn ($listing) => (float) $listing->price >= (float) $request->min_price
            ))
            ->when($request->filled('max_price'), fn (Collection $items) => $items->filter(
                fn ($listing) => (float) $listing->price <= (float) $request->max_price
            ))
            ->when($request->filled('search'), function (Collection $items) use ($request) {
                $needle = Str::lower(trim((string) $request->search));

                return $items->filter(function ($listing) use ($needle) {
                    return Str::contains(Str::lower((string) $listing->propertyname), $needle)
                        || Str::contains(Str::lower((string) $listing->area), $needle);
                });
            })
            ->values();
    }

    private function decorateListing(Listing|CondoListing $listing, string $source): Listing|CondoListing
    {
        $listing->setAttribute('id', $listing instanceof CondoListing ? $listing->getKey() : $listing->id);
        $listing->setAttribute('source_key', $source);
        $listing->setAttribute('source_label', strtoupper($source));

        return $listing;
    }

    private function paginateCollection(Collection $listings, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));
        $items = $listings->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $listings->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }
}
