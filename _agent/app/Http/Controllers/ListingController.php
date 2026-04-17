<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithCondoFeatureGate;
use App\Models\CondoListing;
use App\Models\IcpListing;
use App\Models\Listing;
use App\Models\State;
use App\Support\CondoPackageManager;
use App\Support\CondoWordpressBridge;
use App\Support\ListingEditor;
use App\Support\RecentlyDeletedService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ListingController extends Controller
{
    use InteractsWithCondoFeatureGate;

    private const INDEX_SOURCES = [
        'all' => 'All',
        'ipp' => 'IPP',
        'icp' => 'ICP',
        'condo' => 'Condo',
    ];

    private const CREATE_SOURCES = [
        'ipp' => 'IPP',
        'icp' => 'ICP',
        'condo' => 'Condo',
    ];

    private const INDEX_PER_PAGE = 12;

    public function __construct(
        private readonly CondoPackageManager $condoPackageManager,
        private readonly RecentlyDeletedService $recentlyDeletedService
    )
    {
    }

    public function index(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;
        $activeSource = $this->resolveListingSource($request->query('source'));
        [$sortBy, $sortDir] = $this->resolveSortOptions($request);

        if ($activeSource === 'condo' && ! $this->canManageSource('condo')) {
            return $this->condoFeatureAccessResponse(
                $this->condoPackageManager,
                'Condo Listings',
                'Condo Listings',
                'Syncing and managing condo listings from Laravel requires Condo Premium Package or Condo Premium Lite Package.',
                [
                    'Condo Premium Package gives 500 listing spaces and 100 daily credits.',
                    'Condo Premium Lite Package gives 100 listing spaces and 50 daily credits.',
                    'Social schedules use 1 daily credit each. Condo Premium Lite also uses daily credit when an article is published or scheduled.',
                    'Delete a condo listing and the slot is returned automatically.',
                ]
            );
        }

        $listings = $this->resolveIndexListings($username, $request, $activeSource, $sortBy, $sortDir);
        [$listingTypes, $propertyTypes, $states] = $this->resolveIndexFilters($username, $activeSource);

        $sourceCounts = $this->resolveSourceCounts($username);

        $sourceTabs = collect(self::INDEX_SOURCES)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'count' => $sourceCounts[$key] ?? null,
                'locked' => $key === 'condo' && ! $this->canManageSource('condo'),
            ])
            ->values();
        $bulkTargetSources = collect($this->createSourceTabs())
            ->filter(fn (array $tab) => $tab['enabled'])
            ->values()
            ->all();
        $condoPackageSummary = $this->condoPackageManager->summaryForAgent($agent);

        return view('listings.index', compact(
            'listings',
            'listingTypes',
            'propertyTypes',
            'states',
            'activeSource',
            'sourceTabs',
            'bulkTargetSources',
            'condoPackageSummary'
        ));
    }

    public function create(Request $request)
    {
        $activeCreateSource = $this->resolveCreateSource($request->query('source'));

        if ($activeCreateSource === 'condo' && ! $this->canCreateSource('condo')) {
            return $this->condoFeatureAccessResponse(
                $this->condoPackageManager,
                'Condo Listings',
                'Condo Listings',
                'Adding condo listings is locked until this account has a condo package.'
            );
        }

        $form = ListingEditor::formData();

        if ($activeCreateSource === 'icp') {
            $form['cobroke'] = 1;
        }

        return view('listings.create', $this->formViewData($form, null, $activeCreateSource));
    }

    public function store(Request $request)
    {
        $source = $this->resolveCreateSource($request->input('source', $request->query('source')));

        if (! $this->canCreateSource($source)) {
            throw ValidationException::withMessages([
                'source' => $this->unavailableSourceMessage($source),
            ]);
        }

        $validated = $this->validateListing($request, $source);
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;

        if ($source === 'condo') {
            $this->condoPackageManager->ensureCondoListingCapacity($agent);
        }

        $listing = $this->newListingModelForSource($source);
        $uploadedPhotoPaths = [];

        try {
            DB::connection($this->connectionForSource($source))->transaction(function () use ($validated, $username, $listing, &$uploadedPhotoPaths) {
                $propertyId = $this->generatePropertyId();
                $uploadedPhotoPaths = $this->storeUploadedImages(
                    $source,
                    $username,
                    $propertyId,
                    $validated['new_images'] ?? [],
                    $validated['existing_photos'] ?? []
                );

                $this->persistListing($listing, $validated, $username, true, $propertyId, $uploadedPhotoPaths);
            });
        } catch (Throwable $exception) {
            $this->deleteLocalPhotos($uploadedPhotoPaths, $source);

            throw $exception;
        }

        return redirect()
            ->route('listings.show', array_filter([
                'id' => $listing instanceof CondoListing ? $listing->getKey() : $listing->id,
                'source' => $source === 'ipp' ? null : $source,
                'return_source' => $source,
            ], static fn (mixed $value) => $value !== null))
            ->with('success', 'Listing created successfully.');
    }

    public function show(Request $request, $id)
    {
        $source = $this->resolveDetailSource($request->query('source'));

        if ($source === 'condo' && ! $this->canManageSource('condo')) {
            return $this->condoFeatureAccessResponse(
                $this->condoPackageManager,
                'Condo Listings',
                'Condo Listings',
                'Viewing condo listings is locked until this account has a condo package.'
            );
        }

        $returnSource = $this->resolveListingSource($request->query('return_source', $source));
        $listing = $this->findOwnedListingOrFail($id, true, $source);

        return view('listings.show', [
            'listing' => $listing,
            'canManageListing' => $this->canManageSource($source),
            'returnSource' => $returnSource,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $source = $this->resolveDetailSource($request->query('source'));

        if ($source === 'condo' && ! $this->canManageSource('condo')) {
            return $this->condoFeatureAccessResponse(
                $this->condoPackageManager,
                'Condo Listings',
                'Condo Listings',
                'Editing condo listings is locked until this account has a condo package.'
            );
        }

        $returnSource = $this->resolveListingSource($request->query('return_source', $source));
        $listing = $this->findOwnedListingOrFail($id, true, $source);

        return view('listings.edit', $this->formViewData(
            ListingEditor::formData($listing),
            $listing,
            $source,
            $source,
            $returnSource
        ));
    }

    public function update(Request $request, $id)
    {
        $currentSource = $this->resolveDetailSource($request->input('original_source', $request->query('source')));
        $targetSource = $this->resolveCreateSource($request->input('source', $currentSource));
        $returnSource = $this->resolveListingSource($request->input('return_source', $request->query('return_source', $currentSource)));

        if (
            (($currentSource === 'condo') || ($targetSource === 'condo'))
            && ! $this->canCreateSource('condo')
        ) {
            return $this->condoFeatureAccessResponse(
                $this->condoPackageManager,
                'Condo Listings',
                'Condo Listings',
                'Updating condo listings is locked until this account has a condo package.'
            );
        }

        if (! $this->canCreateSource($targetSource)) {
            throw ValidationException::withMessages([
                'source' => $this->unavailableSourceMessage($targetSource),
            ]);
        }

        $validated = $this->validateListing($request, $targetSource);
        $listing = $this->findOwnedListingOrFail($id, true, $currentSource);
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;
        $uploadedPhotoPaths = [];
        $removedLocalPhotos = [];
        $activeListing = $listing;

        if ($currentSource !== 'condo' && $targetSource === 'condo') {
            $this->condoPackageManager->ensureCondoListingCapacity($agent);
        }

        if ($targetSource === $currentSource) {
            try {
                DB::connection($this->connectionForSource($currentSource))->transaction(function () use (
                    $listing,
                    $validated,
                    $username,
                    &$uploadedPhotoPaths,
                    &$removedLocalPhotos
                ) {
                    $uploadedPhotoPaths = $this->storeUploadedImages(
                        $currentSource,
                        $username,
                        (string) $listing->propertyid,
                        $validated['new_images'] ?? [],
                        $validated['existing_photos'] ?? []
                    );

                    $removedLocalPhotos = $this->persistListing(
                        $listing,
                        $validated,
                        $username,
                        false,
                        (string) $listing->propertyid,
                        $uploadedPhotoPaths
                    );
                });
            } catch (Throwable $exception) {
                $this->deleteLocalPhotos($uploadedPhotoPaths, $currentSource);

                throw $exception;
            }
        } else {
            [$activeListing, $uploadedPhotoPaths, $removedLocalPhotos] = $this->moveListingToSource(
                $listing,
                $currentSource,
                $targetSource,
                $validated,
                $username
            );
        }

        $this->deleteLocalPhotos($removedLocalPhotos, $targetSource);

        return redirect()
            ->route('listings.show', array_filter([
                'id' => $activeListing instanceof CondoListing ? $activeListing->getKey() : $activeListing->id,
                'source' => $targetSource === 'ipp' ? null : $targetSource,
                'return_source' => $returnSource,
            ], static fn (mixed $value) => $value !== null))
            ->with('success', 'Listing updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $source = $this->resolveDetailSource($request->input('source', $request->query('source')));

        if ($source === 'condo' && ! $this->canManageSource('condo')) {
            return $this->condoFeatureAccessResponse(
                $this->condoPackageManager,
                'Condo Listings',
                'Condo Listings',
                'Managing condo listings is locked until this account has a condo package.'
            );
        }

        $returnSource = $this->resolveListingSource($request->input('return_source', $request->query('return_source', $source)));
        $listing = $this->findOwnedListingOrFail($id, true, $source);

        $this->trashListing($listing, $source, Auth::guard('agent')->user()->username);

        return redirect()
            ->route('listings.index', ['source' => $returnSource])
            ->with('success', 'Listing moved to Recently Deleted.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => ['required', 'string'],
            'selection_mode' => ['nullable', 'string'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['string'],
            'excluded' => ['nullable', 'array'],
            'excluded.*' => ['string'],
            'target_source' => ['nullable', 'string'],
            'return_source' => ['nullable', 'string'],
            'return_filters' => ['nullable', 'array'],
        ]);

        $action = strtolower(trim((string) $request->input('action')));

        if (! in_array($action, ['delete', 'migrate'], true)) {
            throw ValidationException::withMessages([
                'action' => 'Choose a valid bulk action.',
            ]);
        }

        $targetSource = null;

        if ($action === 'migrate') {
            $rawTargetSource = strtolower(trim((string) $request->input('target_source')));

            if ($rawTargetSource === '' || ! array_key_exists($rawTargetSource, self::CREATE_SOURCES)) {
                throw ValidationException::withMessages([
                    'target_source' => 'Select a valid destination source for the copied listings.',
                ]);
            }

            $targetSource = $rawTargetSource;

            if (! $this->canCreateSource($targetSource)) {
                throw ValidationException::withMessages([
                    'target_source' => $this->unavailableSourceMessage($targetSource),
                ]);
            }
        }

        $username = Auth::guard('agent')->user()->username;
        $selections = $this->resolveBulkSelections($request, $username);

        if ($selections->isEmpty()) {
            throw ValidationException::withMessages([
                'selected' => 'Select at least one listing first.',
            ]);
        }

        $completed = 0;
        $skipped = [];

        foreach ($selections as $selection) {
            try {
                $listing = $this->findOwnedListingOrFail($selection['id'], true, $selection['source']);

                if ($action === 'delete') {
                    $this->trashListing($listing, $selection['source'], $username);
                    $completed++;
                    continue;
                }

                $this->copyListingToSource($listing, $selection['source'], $targetSource, $username);
                $completed++;
            } catch (ValidationException $exception) {
                $message = collect($exception->errors())->flatten()->first() ?: 'This listing could not be processed.';
                $skipped[] = $this->bulkSelectionLabel($selection) . ': ' . $message;
            } catch (Throwable $exception) {
                $skipped[] = $this->bulkSelectionLabel($selection) . ': This listing could not be processed right now.';
            }
        }

        $requestedCount = $selections->count();
        $success = match ($action) {
            'delete' => $completed > 0
                ? 'Moved ' . $completed . ' selected listing' . ($completed === 1 ? '' : 's') . ' to Recently Deleted.'
                : null,
            default => $completed > 0
                ? 'Copied ' . $completed . ' selected listing' . ($completed === 1 ? '' : 's') . ' into ' . strtoupper((string) $targetSource) . '.'
                : null,
        };

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => $completed > 0 || $skipped === [],
                'action' => $action,
                'target_source' => $targetSource,
                'requested' => $requestedCount,
                'completed' => $completed,
                'skipped' => $skipped,
                'message' => $success ?? 'No listings were processed.',
                'redirect_url' => route('listings.index', $this->bulkReturnQuery($request)),
            ]);
        }

        $redirect = redirect()->route('listings.index', $this->bulkReturnQuery($request));

        if ($success !== null) {
            $redirect->with('success', $success);
        }

        if ($skipped !== []) {
            $redirect->withErrors($skipped);
        }

        return $redirect;
    }

    private function resolveBulkSelections(Request $request, string $username): Collection
    {
        $selectionMode = strtolower(trim((string) $request->input('selection_mode', 'manual')));

        if ($selectionMode === 'all_filtered') {
            $source = $this->resolveListingSource($request->input('return_source'));
            $excludedTokens = collect($request->input('excluded', []))
                ->map(fn (mixed $value) => $this->parseBulkSelectionToken($value))
                ->filter()
                ->pluck('token')
                ->unique()
                ->values()
                ->all();

            return $this->resolveFilteredSelections($username, $this->bulkFilterRequest($request, $source), $source)
                ->reject(fn (array $selection) => in_array($selection['token'], $excludedTokens, true))
                ->values();
        }

        return collect($request->input('selected', []))
            ->map(fn (mixed $value) => $this->parseBulkSelectionToken($value))
            ->filter()
            ->unique(fn (array $selection) => $selection['token'])
            ->values();
    }

    private function resolveStates(string $username)
    {
        try {
            return State::whereNotNull('state')
                ->where('state', '!=', '')
                ->orderBy('state')
                ->get();
        } catch (Throwable) {
            return $this->ownedListingsQuery($username)
                ->whereNotNull('state')
                ->where('state', '!=', '')
                ->select('state')
                ->distinct()
                ->orderBy('state')
                ->get();
        }
    }

    private function ownedListingsQuery(string $username)
    {
        return Listing::query()
            ->active()
            ->where('username', $username);
    }

    private function ownedIcpListingsQuery(string $username)
    {
        return IcpListing::query()
            ->active()
            ->where('username', $username);
    }

    private function ownedCondoListingsCollection(string $username, bool $withRelations = false): Collection
    {
        $query = CondoListing::query()->active()->with('details');

        if ($withRelations) {
            $query->with(['agent.detail']);
        }

        return $query->get()
            ->filter(fn (CondoListing $listing) => $listing->username === $username)
            ->values();
    }

    private function findOwnedListingOrFail(int|string $id, bool $withRelations = false, string $source = 'ipp'): Listing|CondoListing
    {
        if (! $this->sourceAvailable($source)) {
            abort(404);
        }

        if ($source === 'condo') {
            $query = CondoListing::query()->active()->with('details');

            if ($withRelations) {
                $query->with(['agent.detail']);
            }

            /** @var CondoListing $listing */
            $listing = $query->findOrFail($id);

            if ($listing->username !== Auth::guard('agent')->user()->username) {
                abort(403);
            }

            return $this->decorateListing($listing, $source, true, $source);
        }

        $query = $this->queryForSource($source)->active();

        if ($withRelations) {
            $query->with(['details', 'agent.detail']);
        }

        $listing = $query->findOrFail($id);

        if ($listing->username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }

        return $this->decorateListing($listing, $source, $this->canManageSource($source), $source);
    }

    private function queryForSource(string $source)
    {
        return match ($source) {
            'icp' => IcpListing::query(),
            'condo' => CondoListing::query(),
            default => Listing::query(),
        };
    }

    private function resolveIndexListings(
        string $username,
        Request $request,
        string $source,
        string $sortBy,
        string $sortDir
    ): LengthAwarePaginator {
        if ($source === 'all') {
            $merged = collect([
                'ipp' => $this->ownedListingsQuery($username),
                'icp' => $this->canManageSource('icp') ? $this->ownedIcpListingsQuery($username) : null,
            ])->filter();

            $listings = $merged->flatMap(function ($query, string $sourceKey) use ($request) {
                return $this->applyListingFilters($query, $request)
                    ->get()
                    ->map(fn ($listing) => $this->decorateListing($listing, $sourceKey, true, 'all'));
            });

            if ($this->canManageSource('condo')) {
                $listings = $listings->concat(
                    $this->applyListingFiltersToCollection($this->ownedCondoListingsCollection($username), $request)
                        ->map(fn ($listing) => $this->decorateListing($listing, 'condo', true, 'all'))
                );
            }

            return $this->paginateCollection(
                $this->sortListings($listings, $sortBy, $sortDir),
                $request
            );
        }

        if (! $this->canManageSource($source)) {
            return $this->emptyIndexPaginator($request);
        }

        if ($source === 'condo') {
            return $this->paginateCollection(
                $this->sortListings(
                    $this->applyListingFiltersToCollection($this->ownedCondoListingsCollection($username), $request)
                        ->map(fn ($listing) => $this->decorateListing($listing, 'condo', true, 'condo')),
                    $sortBy,
                    $sortDir
                ),
                $request
            );
        }

        $query = match ($source) {
            'icp' => $this->ownedIcpListingsQuery($username),
            default => $this->ownedListingsQuery($username),
        };

        $paginator = $this->applyListingFilters($query, $request)
            ->orderBy($sortBy, $sortDir)
            ->paginate(self::INDEX_PER_PAGE)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn ($listing) => $this->decorateListing(
                    $listing,
                    $source,
                    $this->canManageSource($source),
                    $source
                )
            )
        );

        return $paginator;
    }

    private function resolveIndexFilters(string $username, string $source): array
    {
        $queries = match ($source) {
            'ipp' => [$this->ownedListingsQuery($username)],
            'icp' => $this->canManageSource('icp') ? [$this->ownedIcpListingsQuery($username)] : [],
            'all' => array_values(array_filter([
                $this->ownedListingsQuery($username),
                $this->canManageSource('icp') ? $this->ownedIcpListingsQuery($username) : null,
            ])),
            default => [],
        };

        $collections = match ($source) {
            'condo' => $this->canManageSource('condo') ? [$this->ownedCondoListingsCollection($username)] : [],
            'all' => $this->canManageSource('condo') ? [$this->ownedCondoListingsCollection($username)] : [],
            default => [],
        };

        return [
            $this->distinctListingValues($queries, 'listingtype', $collections),
            $this->distinctListingValues($queries, 'propertytype', $collections),
            $this->distinctListingValues($queries, 'state', $collections),
        ];
    }

    private function distinctListingValues(array $queries, string $column, array $collections = []): Collection
    {
        return collect($queries)
            ->flatMap(function ($query) use ($column) {
                $sourceQuery = clone $query;

                return $sourceQuery
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->distinct()
                    ->pluck($column);
            })
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->concat(
                collect($collections)
                    ->flatMap(fn (Collection $listings) => $listings->pluck($column))
                    ->map(fn (mixed $value) => trim((string) $value))
                    ->filter()
            )
            ->unique(fn (string $value) => Str::lower($value))
            ->sortBy(fn (string $value) => Str::lower($value))
            ->values();
    }

    private function applyListingFilters($query, Request $request)
    {
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

        return $query;
    }

    private function applyListingFiltersToCollection(Collection $listings, Request $request): Collection
    {
        return $listings
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

    private function resolveSortOptions(Request $request): array
    {
        $allowedSorts = ['createddate', 'updateddate', 'price', 'propertyname', 'listingtype', 'propertytype', 'state', 'area'];
        $sortBy = $request->get('sort', 'createddate');
        $sortBy = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'createddate';

        $sortDir = strtolower($request->get('dir', 'desc'));
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        return [$sortBy, $sortDir];
    }

    private function resolveListingSource(?string $source): string
    {
        $source = strtolower(trim((string) $source));

        return array_key_exists($source, self::INDEX_SOURCES) ? $source : 'all';
    }

    private function resolveCreateSource(?string $source): string
    {
        $source = strtolower(trim((string) $source));

        return array_key_exists($source, self::CREATE_SOURCES) ? $source : 'ipp';
    }

    private function resolveDetailSource(?string $source): string
    {
        $source = strtolower(trim((string) $source));

        return in_array($source, ['icp', 'condo'], true) ? $source : 'ipp';
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

    private function resolveSourceCounts(string $username): array
    {
        $ippCount = $this->ownedListingsQuery($username)->count();

        $icpCount = $this->canManageSource('icp')
            ? $this->ownedIcpListingsQuery($username)->count()
            : 0;

        $condoCount = $this->canManageSource('condo')
            ? $this->ownedCondoListingsCollection($username)->count()
            : 0;

        return [
            'ipp' => $ippCount,
            'icp' => $icpCount,
            'condo' => $condoCount,
            'all' => $ippCount + $icpCount + $condoCount,
        ];
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

    private function unavailableSourceMessage(string $source): string
    {
        return match ($source) {
            'icp' => 'ICP listing tables are not available on this environment right now.',
            'condo' => ! $this->sourceAvailable('condo')
                ? 'The WordPress condo property tables are not available on this environment right now.'
                : 'Subscribe to Condo Premium Package or Condo Premium Lite Package to unlock condo listings.',
            default => 'The selected listing source is not available right now.',
        };
    }

    private function canManageSource(string $source): bool
    {
        return match ($source) {
            'ipp' => true,
            'icp' => $this->sourceAvailable('icp'),
            'condo' => $this->sourceAvailable('condo')
                && Auth::guard('agent')->check()
                && $this->condoPackageManager->hasAccess(Auth::guard('agent')->user()),
            default => false,
        };
    }

    private function canCreateSource(string $source): bool
    {
        return $this->canManageSource($source);
    }

    private function decorateListing(Listing|CondoListing $listing, string $source, bool $canManage, string $returnSource): Listing|CondoListing
    {
        $listing->setAttribute('id', $listing instanceof CondoListing ? $listing->getKey() : $listing->id);
        $listing->setAttribute('source_key', $source);
        $listing->setAttribute('source_label', strtoupper($source));
        $listing->setAttribute('can_manage', $canManage);
        $listing->setAttribute('return_source', $returnSource);

        return $listing;
    }

    private function sortListings(Collection $listings, string $sortBy, string $sortDir): Collection
    {
        return $listings
            ->sortBy(
                fn ($listing) => $this->normalizedSortValue($listing, $sortBy),
                SORT_NATURAL,
                $sortDir === 'desc'
            )
            ->values();
    }

    private function normalizedSortValue(Listing|CondoListing $listing, string $sortBy): string|float
    {
        $value = $listing->getAttribute($sortBy);

        if ($sortBy === 'price') {
            return (float) $value;
        }

        if (in_array($sortBy, ['createddate', 'updateddate'], true)) {
            $numericValue = preg_replace('/\D+/', '', (string) $value);

            return str_pad((string) $numericValue, 14, '0', STR_PAD_LEFT);
        }

        return Str::lower(trim((string) $value));
    }

    private function paginateCollection(Collection $listings, Request $request): LengthAwarePaginator
    {
        $currentPage = max(1, (int) $request->query('page', 1));
        $items = $listings->forPage($currentPage, self::INDEX_PER_PAGE)->values();

        return new LengthAwarePaginator(
            $items,
            $listings->count(),
            self::INDEX_PER_PAGE,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }

    private function emptyIndexPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            self::INDEX_PER_PAGE,
            max(1, (int) $request->query('page', 1)),
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }

    private function validateListing(Request $request, string $source = 'ipp'): array
    {
        $validated = $request->validate([
            'source' => ['nullable', 'string'],
            'original_source' => ['nullable', 'string'],
            'return_source' => ['nullable', 'string'],
            'propertyname' => ['required', 'string', 'max:100'],
            'propertytype' => ['required', 'string', 'max:100'],
            'listingtype' => ['required', 'string'],
            'price' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:100'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'existing_photos' => ['nullable', 'array'],
            'existing_photos.*' => ['string'],
            'new_images' => ['nullable', 'array'],
            'new_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:10240'],
            'cobroke' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string'],
            'township' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:50'],
            'tenure' => ['nullable', 'string', 'max:255'],
            'title_type' => ['nullable', 'string', 'max:255'],
            'land_title_type' => ['nullable', 'string', 'max:255'],
            'occupancy' => ['nullable', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:255'],
            'bumiputera_lot' => ['nullable', 'string', 'max:255'],
            'negotiable' => ['nullable', 'string', 'max:255'],
            'bedrooms' => ['nullable', 'string', 'max:50'],
            'bathrooms' => ['nullable', 'string', 'max:50'],
            'built_up_area' => ['nullable', 'string', 'max:255'],
            'land_area' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'car_park' => ['nullable', 'string', 'max:255'],
            'furnishing' => ['nullable', 'string', 'max:255'],
            'year_built' => ['nullable', 'string', 'max:255'],
            'rent_deposit' => ['nullable', 'string', 'max:255'],
            'auction_date' => ['nullable', 'string', 'max:255'],
            'auction_number' => ['nullable', 'string', 'max:255'],
            'new_launch_units' => ['nullable', 'string', 'max:255'],
            'features' => ['nullable', 'string'],
        ]);

        if (! in_array($validated['listingtype'], ListingEditor::listingTypes(), true)) {
            throw ValidationException::withMessages([
                'listingtype' => 'Select a valid listing type.',
            ]);
        }

        $price = ListingEditor::normalizePrice($validated['price']);

        if ($price === null) {
            throw ValidationException::withMessages([
                'price' => 'Enter a valid numeric price.',
            ]);
        }

        $validated['price'] = $price;
        $validated['cobroke'] = (int) ($validated['cobroke'] ?? 0);
        $validated['source'] = $source;

        return $validated;
    }

    private function formViewData(
        array $form,
        Listing|CondoListing|null $listing = null,
        string $activeCreateSource = 'ipp',
        ?string $originalSource = null,
        ?string $returnSource = null
    ): array {
        $currentSource = $activeCreateSource;

        return [
            'listing' => $listing,
            'form' => $form,
            'states' => $this->resolveStates(Auth::guard('agent')->user()->username),
            'listingTypes' => ListingEditor::listingTypes(),
            'propertyTypes' => ListingEditor::propertyTypes(),
            'generalFieldGroups' => ListingEditor::generalFieldGroups(),
            'generalSectionTitles' => ListingEditor::sectionTitles(),
            'activeCreateSource' => $currentSource,
            'editingSource' => $currentSource,
            'originalSource' => $originalSource ?? $currentSource,
            'returnSource' => $returnSource ?? $currentSource,
            'createSourceTabs' => $this->createSourceTabs(),
        ];
    }

    private function trashListing(Listing|CondoListing $listing, string $source, string $username): void
    {
        $this->recentlyDeletedService->rememberListing($listing, $source, $username);

        if ($source === 'condo') {
            DB::connection($this->connectionForSource($source))->transaction(function () use ($listing) {
                $now = now();

                $listing->post_status = 'trash';
                $listing->post_modified = $now->format('Y-m-d H:i:s');
                $listing->post_modified_gmt = $now->clone()->utc()->format('Y-m-d H:i:s');
                $listing->save();
            });

            return;
        }

        DB::connection($this->connectionForSource($source))->transaction(function () use ($listing, $source) {
            $this->retireListing($listing, $source, false);
        });
    }

    private function persistListing(
        Listing|CondoListing $listing,
        array $validated,
        string $username,
        bool $creating,
        string $propertyId,
        array $uploadedPhotoPaths = [],
        ?string $createdDate = null,
        array $baselinePhotoPaths = [],
        Listing|CondoListing|null $detailTemplate = null
    ): array {
        $timestamp = Carbon::now()->format('YmdHis');
        $currentPhotoPaths = $creating
            ? ListingEditor::normalizePhotoPaths($baselinePhotoPaths)
            : ListingEditor::photoPaths($listing);
        $retainedPhotoPaths = array_values(array_intersect(
            $currentPhotoPaths,
            ListingEditor::normalizePhotoPaths($validated['existing_photos'] ?? [])
        ));
        $photoPaths = ListingEditor::mergePhotoPaths($retainedPhotoPaths, $uploadedPhotoPaths);
        $removedPhotoPaths = array_values(array_diff($currentPhotoPaths, $photoPaths));

        $attributes = [
            'propertyname' => trim($validated['propertyname']),
            'propertytype' => trim($validated['propertytype']),
            'listingtype' => $validated['listingtype'],
            'price' => $validated['price'],
            'state' => trim($validated['state']),
            'area' => trim($validated['area']),
            'keywords' => ListingEditor::buildKeywords($validated),
            'totalphoto' => count($photoPaths),
            'photopath' => $photoPaths[0] ?? '',
            'cobroke' => (int) $validated['cobroke'],
            'updateddate' => $timestamp,
            'isDeleted' => 0,
        ];

        if ($creating) {
            $attributes['username'] = $username;
            $attributes['propertyid'] = $propertyId;
            $attributes['createddate'] = $createdDate ?: $timestamp;
        }

        $listing->fill($attributes);
        $listing->save();

        foreach (ListingEditor::detailPayload($validated, $photoPaths, $detailTemplate ?? ($creating ? null : $listing)) as $key => $value) {
            $detail = $listing->details()->where('meta_key', $key)->first();

            if ($detail) {
                $detail->meta_value = $value;
                $detail->save();
                continue;
            }

            $listing->details()->create([
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
        }

        if ($listing instanceof CondoListing) {
            app(CondoWordpressBridge::class)->syncListing($listing, $validated, $username, $propertyId, $photoPaths);

            return [];
        }

        return $removedPhotoPaths;
    }

    /**
     * @return array{0: Listing|CondoListing, 1: array<int, string>, 2: array<int, string>}
     */
    private function moveListingToSource(
        Listing|CondoListing $listing,
        string $currentSource,
        string $targetSource,
        array $validated,
        string $username
    ): array {
        $propertyId = (string) $listing->propertyid;
        $createdDate = $this->legacyCreatedDateFor($listing);
        $baselinePhotoPaths = ListingEditor::photoPaths($listing);
        $uploadedPhotoPaths = [];
        $removedLocalPhotos = [];
        $targetListing = $this->newListingModelForSource($targetSource);
        $targetConnection = $this->connectionForSource($targetSource);
        $currentConnection = $this->connectionForSource($currentSource);

        if ($this->propertyIdExistsInSource($propertyId, $targetSource)) {
            throw ValidationException::withMessages([
                'source' => 'This listing cannot be moved because the target source already contains property ID ' . $propertyId . '.',
            ]);
        }

        $persistTarget = function () use (
            $listing,
            $validated,
            $username,
            $propertyId,
            $createdDate,
            $baselinePhotoPaths,
            &$uploadedPhotoPaths,
            &$removedLocalPhotos,
            $targetListing,
            $targetSource
        ) {
            $uploadedPhotoPaths = $this->storeUploadedImages(
                $targetSource,
                $username,
                $propertyId,
                $validated['new_images'] ?? [],
                $validated['existing_photos'] ?? []
            );

            $removedLocalPhotos = $this->persistListing(
                $targetListing,
                $validated,
                $username,
                true,
                $propertyId,
                $uploadedPhotoPaths,
                $createdDate,
                $baselinePhotoPaths,
                $listing
            );
        };

        try {
            if ($targetConnection === $currentConnection) {
                DB::connection($targetConnection)->transaction(function () use (
                    $persistTarget,
                    $listing,
                    $currentSource,
                    $targetSource
                ) {
                    $persistTarget();
                    $this->retireListing($listing, $currentSource, ! ($currentSource === 'condo' && $targetSource !== 'condo'));
                });
            } else {
                DB::connection($targetConnection)->transaction($persistTarget);

                try {
                    DB::connection($currentConnection)->transaction(function () use ($listing, $currentSource, $targetSource) {
                        $this->retireListing($listing, $currentSource, ! ($currentSource === 'condo' && $targetSource !== 'condo'));
                    });
                } catch (Throwable $exception) {
                    DB::connection($targetConnection)->transaction(function () use ($targetListing) {
                        $targetListing->details()->delete();
                        $targetListing->delete();
                    });

                    throw $exception;
                }
            }
        } catch (Throwable $exception) {
            $this->deleteLocalPhotos($uploadedPhotoPaths, $targetSource);

            throw $exception;
        }

        return [
            $this->decorateListing($targetListing->fresh(['details', 'agent.detail']), $targetSource, true, $targetSource),
            $uploadedPhotoPaths,
            $removedLocalPhotos,
        ];
    }

    private function copyListingToSource(
        Listing|CondoListing $listing,
        string $currentSource,
        string $targetSource,
        string $username
    ): Listing|CondoListing {
        if ($currentSource === $targetSource) {
            throw ValidationException::withMessages([
                'target_source' => 'This listing is already in ' . strtoupper($targetSource) . '.',
            ]);
        }

        $propertyId = (string) $listing->propertyid;

        if ($this->propertyIdExistsInSource($propertyId, $targetSource)) {
            throw ValidationException::withMessages([
                'target_source' => 'The target source already contains property ID ' . $propertyId . '.',
            ]);
        }

        if ($targetSource === 'condo') {
            $this->condoPackageManager->ensureCondoListingCapacity(Auth::guard('agent')->user());
        }

        $targetListing = $this->newListingModelForSource($targetSource);
        $validated = $this->copyPayloadFromListing($listing, $targetSource);

        DB::connection($this->connectionForSource($targetSource))->transaction(function () use (
            $targetListing,
            $validated,
            $username,
            $propertyId,
            $listing
        ) {
            $this->persistListing(
                $targetListing,
                $validated,
                $username,
                true,
                $propertyId,
                [],
                $this->legacyCreatedDateFor($listing),
                ListingEditor::photoPaths($listing),
                $listing
            );
        });

        /** @var Listing|CondoListing $freshListing */
        $freshListing = $targetListing->fresh(['details', 'agent.detail']);

        return $this->decorateListing($freshListing, $targetSource, true, $targetSource);
    }

    private function retireListing(Listing|CondoListing $listing, string $source, bool $deleteMedia = true): void
    {
        if ($source === 'condo' && $deleteMedia) {
            app(CondoWordpressBridge::class)->deleteListingAssets($listing);
        }

        match ($source) {
            'icp' => $this->archiveIcpListing($listing),
            'ipp' => $this->archiveIppListing($listing),
            default => null,
        };

        $listing->details()->delete();
        $listing->delete();
    }

    private function generatePropertyId(): string
    {
        $base = Carbon::now()->format('YmdHis');
        $propertyId = $base;
        $suffix = 0;

        while ($this->propertyIdExists($propertyId)) {
            $suffix++;
            $propertyId = $base . sprintf('%02d', $suffix);
        }

        return $propertyId;
    }

    private function archiveIppListing(Listing $listing): void
    {
        if (! Schema::connection('mysql2')->hasTable('softdeleteposts') || ! Schema::connection('mysql2')->hasTable('softdeletepostdetails')) {
            return;
        }

        $connection = DB::connection('mysql2');
        $deletedDate = Carbon::now()->format('YmdHis');

        $connection->table('softdeleteposts')->insert([
            'id' => $listing->id,
            'username' => $listing->username,
            'propertyid' => $listing->propertyid,
            'propertyname' => $listing->propertyname,
            'propertytype' => $listing->propertytype,
            'price' => $listing->price,
            'listingtype' => $listing->listingtype,
            'state' => $listing->state,
            'area' => $listing->area,
            'keywords' => (string) $listing->getRawOriginal('keywords'),
            'totalphoto' => (int) $listing->getRawOriginal('totalphoto'),
            'photopath' => (string) $listing->getRawOriginal('photopath'),
            'cobroke' => (int) $listing->getRawOriginal('cobroke'),
            'createddate' => (string) $listing->getRawOriginal('createddate'),
            'updateddate' => (string) $listing->getRawOriginal('updateddate'),
            'isDeleted' => 1,
            'type' => 'ipp',
            'deleteddate' => $deletedDate,
        ]);

        foreach ($listing->details as $detail) {
            $connection->table('softdeletepostdetails')->insert([
                'id' => $detail->id,
                'postid' => $detail->postid,
                'meta_key' => $detail->meta_key,
                'meta_value' => $detail->meta_value,
                'type' => 'ipp',
            ]);
        }
    }

    private function archiveIcpListing(Listing $listing): void
    {
        $connectionName = $this->connectionForSource('icp');

        if (
            ! Schema::connection($connectionName)->hasTable('softdeletemobileposts')
            || ! Schema::connection($connectionName)->hasTable('softdeletemobilepostdetails')
        ) {
            return;
        }

        $connection = DB::connection($connectionName);

        $connection->table('softdeletemobileposts')->insert([
            'id' => $listing->id,
            'username' => $listing->username,
            'propertyid' => $listing->propertyid,
            'propertyname' => $listing->propertyname,
            'propertytype' => $listing->propertytype,
            'price' => $listing->price,
            'listingtype' => $listing->listingtype,
            'state' => $listing->state,
            'area' => $listing->area,
            'keywords' => (string) $listing->getRawOriginal('keywords'),
            'totalphoto' => (int) $listing->getRawOriginal('totalphoto'),
            'photopath' => (string) $listing->getRawOriginal('photopath'),
            'cobroke' => (int) $listing->getRawOriginal('cobroke'),
            'createddate' => (string) $listing->getRawOriginal('createddate'),
            'updateddate' => (string) $listing->getRawOriginal('updateddate'),
            'isDeleted' => 1,
        ]);

        foreach ($listing->details as $detail) {
            $connection->table('softdeletemobilepostdetails')->insert([
                'id' => $detail->id,
                'postid' => $detail->postid,
                'meta_key' => $detail->meta_key,
                'meta_value' => $detail->meta_value,
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    private function storeUploadedImages(string $source, string $username, string $propertyId, array $files, array $existingPhotoPaths = []): array
    {
        if ($source === 'condo') {
            return app(CondoWordpressBridge::class)->storeUploadedImages($propertyId, $files, $existingPhotoPaths);
        }

        $storedPaths = [];
        $directory = 'Database/Images/' . trim($propertyId);
        $sequence = $this->nextLegacyPhotoSequence($existingPhotoPaths);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            [$payload, $extension] = $this->prepareLegacyImagePayload($file);

            if ($payload === '') {
                continue;
            }

            $filename = sprintf('%03d.%s', $sequence, $extension);
            $relativePath = $directory . '/' . $filename;

            Storage::disk('public')->put($relativePath, $payload);
            $storedPaths[] = $relativePath;
            $sequence++;
        }

        return $storedPaths;
    }

    private function deleteLocalPhotos(array $paths, string $source): void
    {
        if ($source === 'condo') {
            app(CondoWordpressBridge::class)->deleteStoredImages($paths);

            return;
        }

        foreach (array_unique(ListingEditor::normalizePhotoPaths($paths)) as $path) {
            $storagePath = ListingEditor::localStoragePhotoPath($path);

            if ($storagePath) {
                Storage::disk('public')->delete($storagePath);
            }
        }
    }

    private function createSourceTabs(): array
    {
        return collect(self::CREATE_SOURCES)
            ->map(function (string $label, string $key) {
                $enabled = $this->canCreateSource($key);
                $sourceAvailable = $this->sourceAvailable($key);

                return [
                    'key' => $key,
                    'label' => $label,
                    'enabled' => $enabled,
                    'description' => match ($key) {
                        'icp' => $enabled
                            ? 'Writes to MobilePosts and keeps the Flutter-style Database/Images photo paths.'
                            : 'ICP listing tables are not available on this environment right now.',
                        'condo' => $enabled
                            ? 'Writes to WordPress properties in wp_condo so Estatik and Rank Math can use the same listings.'
                            : ($sourceAvailable
                                ? 'Requires Condo Premium Package or Condo Premium Lite Package before condo listings can be added here.'
                                : 'The WordPress condo property tables are not available on this environment right now.'),
                        default => 'Writes to Posts and keeps the same legacy Database/Images photo path contract.',
                    },
                ];
            })
            ->values()
            ->all();
    }

    private function connectionForSource(string $source): string
    {
        return match ($source) {
            'icp' => IcpListing::resolvedConnectionName(),
            'condo' => (new CondoListing())->getConnectionName(),
            default => (new Listing())->getConnectionName(),
        };
    }

    private function newListingModelForSource(string $source): Listing|CondoListing
    {
        return match ($source) {
            'icp' => new IcpListing(),
            'condo' => new CondoListing(),
            default => new Listing(),
        };
    }

    private function propertyIdExists(string $propertyId): bool
    {
        if ($this->propertyIdExistsInSource($propertyId, 'ipp')) {
            return true;
        }

        if ($this->sourceAvailable('icp') && $this->propertyIdExistsInSource($propertyId, 'icp')) {
            return true;
        }

        return $this->sourceAvailable('condo') && $this->propertyIdExistsInSource($propertyId, 'condo');
    }

    private function propertyIdExistsInSource(string $propertyId, string $source): bool
    {
        if (! $this->sourceAvailable($source)) {
            return false;
        }

        if ($source === 'condo') {
            return app(CondoWordpressBridge::class)->propertyIdExists($propertyId);
        }

        return $this->queryForSource($source)
            ->where('propertyid', $propertyId)
            ->exists();
    }

    private function copyPayloadFromListing(Listing|CondoListing $listing, string $targetSource): array
    {
        $payload = ListingEditor::formData($listing);
        $payload['source'] = $targetSource;
        $payload['existing_photos'] = ListingEditor::photoPaths($listing);
        $payload['new_images'] = [];
        $payload['cobroke'] = (int) ($payload['cobroke'] ?? 0);

        return $payload;
    }

    private function legacyCreatedDateFor(Listing|CondoListing $listing): string
    {
        $createdDate = trim((string) ($listing->createddate ?? $listing->getRawOriginal('createddate')));

        return $createdDate !== '' ? $createdDate : Carbon::now()->format('YmdHis');
    }

    private function bulkFilterRequest(Request $request, string $source): Request
    {
        $payload = collect($request->input('return_filters', []))
            ->map(function (mixed $value) {
                if (is_array($value)) {
                    return array_values(array_filter($value, fn (mixed $item) => $item !== null && $item !== ''));
                }

                return $value;
            })
            ->filter(fn (mixed $value) => $value !== null && $value !== '' && $value !== [])
            ->all();

        $payload['source'] = $source;

        return Request::create('/listings', 'GET', $payload);
    }

    private function resolveFilteredSelections(string $username, Request $request, string $source): Collection
    {
        if ($source === 'all') {
            return collect(['ipp', 'icp', 'condo'])
                ->filter(fn (string $sourceKey) => $this->sourceAvailable($sourceKey) && $this->canManageSource($sourceKey))
                ->flatMap(fn (string $sourceKey) => $this->filteredSelectionsForSource($username, $request, $sourceKey))
                ->unique(fn (array $selection) => $selection['token'])
                ->values();
        }

        return $this->filteredSelectionsForSource($username, $request, $source)
            ->unique(fn (array $selection) => $selection['token'])
            ->values();
    }

    private function filteredSelectionsForSource(string $username, Request $request, string $source): Collection
    {
        if (! $this->sourceAvailable($source) || ! $this->canManageSource($source)) {
            return collect();
        }

        if ($source === 'condo') {
            return $this->applyListingFiltersToCollection($this->ownedCondoListingsCollection($username), $request)
                ->map(fn (CondoListing $listing) => [
                    'token' => $source . ':' . $listing->getKey(),
                    'source' => $source,
                    'id' => (int) $listing->getKey(),
                ])
                ->values();
        }

        $query = match ($source) {
            'icp' => $this->ownedIcpListingsQuery($username),
            default => $this->ownedListingsQuery($username),
        };

        return $this->applyListingFilters($query, $request)
            ->get(['id'])
            ->map(fn ($listing) => [
                'token' => $source . ':' . $listing->id,
                'source' => $source,
                'id' => (int) $listing->id,
            ])
            ->values();
    }

    private function parseBulkSelectionToken(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        [$source, $id] = array_pad(explode(':', trim($value), 2), 2, null);
        $source = strtolower(trim((string) $source));
        $id = trim((string) $id);

        if (! in_array($source, ['ipp', 'icp', 'condo'], true) || ! ctype_digit($id)) {
            return null;
        }

        return [
            'token' => $source . ':' . $id,
            'source' => $source,
            'id' => (int) $id,
        ];
    }

    private function bulkSelectionLabel(array $selection): string
    {
        return strtoupper((string) ($selection['source'] ?? 'listing')) . ' #' . ($selection['id'] ?? '?');
    }

    private function bulkReturnQuery(Request $request): array
    {
        $query = collect($request->input('return_filters', []))
            ->filter(fn (mixed $value) => $value !== null && $value !== '')
            ->all();

        $query['source'] = $this->resolveListingSource($request->input('return_source'));

        return $query;
    }

    private function nextLegacyPhotoSequence(array $existingPhotoPaths): int
    {
        $highestSequence = collect(ListingEditor::normalizePhotoPaths($existingPhotoPaths))
            ->map(function (string $path) {
                $filename = pathinfo($path, PATHINFO_FILENAME);

                if (preg_match('/^(\d+)/', $filename, $matches) !== 1) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max();

        return max(1, (int) $highestSequence + 1);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function prepareLegacyImagePayload(UploadedFile $file): array
    {
        $contents = $file->get();

        if (! is_string($contents) || $contents === '') {
            return ['', 'jpg'];
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $image = @imagecreatefromstring($contents);

            if ($image !== false) {
                ob_start();
                imagejpeg($image, null, 85);
                $jpegContents = ob_get_clean();
                imagedestroy($image);

                if (is_string($jpegContents) && $jpegContents !== '') {
                    return [$jpegContents, 'jpg'];
                }
            }
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        return [$contents, $extension];
    }
}
