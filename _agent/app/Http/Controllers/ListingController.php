<?php

namespace App\Http\Controllers;

use App\Models\IcpListing;
use App\Models\Listing;
use App\Models\State;
use App\Support\ListingEditor;
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
    private const INDEX_SOURCES = [
        'all' => 'All',
        'ipp' => 'IPP',
        'icp' => 'ICP',
        'condo' => 'Condo',
    ];

    private const INDEX_PER_PAGE = 12;

    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $activeSource = $this->resolveListingSource($request->query('source'));
        [$sortBy, $sortDir] = $this->resolveSortOptions($request);

        $listings = $this->resolveIndexListings($username, $request, $activeSource, $sortBy, $sortDir);
        [$listingTypes, $propertyTypes, $states] = $this->resolveIndexFilters($username, $activeSource);

        $sourceTabs = collect(self::INDEX_SOURCES)
            ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
            ->values();

        return view('listings.index', compact(
            'listings',
            'listingTypes',
            'propertyTypes',
            'states',
            'activeSource',
            'sourceTabs'
        ));
    }

    public function create()
    {
        return view('listings.create', $this->formViewData(ListingEditor::formData()));
    }

    public function store(Request $request)
    {
        $validated = $this->validateListing($request);
        $username = Auth::guard('agent')->user()->username;
        $listing = new Listing();
        $uploadedPhotoPaths = [];

        try {
            DB::connection('mysql2')->transaction(function () use ($validated, $username, $listing, &$uploadedPhotoPaths) {
                $propertyId = $this->generatePropertyId();
                $uploadedPhotoPaths = $this->storeUploadedImages(
                    $username,
                    $propertyId,
                    $validated['new_images'] ?? []
                );

                $this->persistListing($listing, $validated, $username, true, $propertyId, $uploadedPhotoPaths);
            });
        } catch (Throwable $exception) {
            $this->deleteLocalPhotos($uploadedPhotoPaths);

            throw $exception;
        }

        return redirect()
            ->route('listings.show', $listing->id)
            ->with('success', 'Listing created successfully.');
    }

    public function show(Request $request, $id)
    {
        $source = $this->resolveDetailSource($request->query('source'));
        $returnSource = $this->resolveListingSource($request->query('return_source', $source));
        $listing = $this->findOwnedListingOrFail($id, true, $source);

        return view('listings.show', [
            'listing' => $listing,
            'canManageListing' => $this->canManageSource($source),
            'returnSource' => $returnSource,
        ]);
    }

    public function edit($id)
    {
        $listing = $this->findOwnedListingOrFail($id, true);

        return view('listings.edit', $this->formViewData(ListingEditor::formData($listing), $listing));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateListing($request);
        $listing = $this->findOwnedListingOrFail($id);
        $username = Auth::guard('agent')->user()->username;
        $uploadedPhotoPaths = [];
        $removedLocalPhotos = [];

        try {
            DB::connection('mysql2')->transaction(function () use ($listing, $validated, $username, &$uploadedPhotoPaths, &$removedLocalPhotos) {
                $uploadedPhotoPaths = $this->storeUploadedImages(
                    $username,
                    (string) $listing->propertyid,
                    $validated['new_images'] ?? []
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
            $this->deleteLocalPhotos($uploadedPhotoPaths);

            throw $exception;
        }

        $this->deleteLocalPhotos($removedLocalPhotos);

        return redirect()
            ->route('listings.show', $listing->id)
            ->with('success', 'Listing updated successfully.');
    }

    public function destroy($id)
    {
        $listing = $this->findOwnedListingOrFail($id, true);
        $localPhotos = ListingEditor::photoPaths($listing);

        DB::connection('mysql2')->transaction(function () use ($listing) {
            $this->archiveListing($listing);
            $listing->details()->delete();
            $listing->delete();
        });

        $this->deleteLocalPhotos($localPhotos);

        return redirect()
            ->route('listings.index')
            ->with('success', 'Listing deleted successfully.');
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

    private function findOwnedListingOrFail(int|string $id, bool $withRelations = false, string $source = 'ipp'): Listing
    {
        if ($source === 'icp' && ! $this->icpSourceAvailable()) {
            abort(404);
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
        if ($source === 'condo') {
            return $this->emptyIndexPaginator($request);
        }

        $icpAvailable = $this->icpSourceAvailable();

        if ($source === 'all') {
            $ippListings = $this->applyListingFilters($this->ownedListingsQuery($username), $request)
                ->get()
                ->map(fn (Listing $listing) => $this->decorateListing($listing, 'ipp', true, 'all'));

            $icpListings = $icpAvailable
                ? $this->applyListingFilters($this->ownedIcpListingsQuery($username), $request)
                    ->get()
                    ->map(fn (Listing $listing) => $this->decorateListing($listing, 'icp', false, 'all'))
                : collect();

            return $this->paginateCollection(
                $this->sortListings($ippListings->concat($icpListings), $sortBy, $sortDir),
                $request
            );
        }

        if ($source === 'icp' && ! $icpAvailable) {
            return $this->emptyIndexPaginator($request);
        }

        $query = $source === 'icp'
            ? $this->ownedIcpListingsQuery($username)
            : $this->ownedListingsQuery($username);

        $paginator = $this->applyListingFilters($query, $request)
            ->orderBy($sortBy, $sortDir)
            ->paginate(self::INDEX_PER_PAGE)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn (Listing $listing) => $this->decorateListing(
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
        $icpAvailable = $this->icpSourceAvailable();

        $queries = match ($source) {
            'ipp' => [$this->ownedListingsQuery($username)],
            'icp' => $icpAvailable ? [$this->ownedIcpListingsQuery($username)] : [],
            'all' => $icpAvailable
                ? [$this->ownedListingsQuery($username), $this->ownedIcpListingsQuery($username)]
                : [$this->ownedListingsQuery($username)],
            default => [],
        };

        return [
            $this->distinctListingValues($queries, 'listingtype'),
            $this->distinctListingValues($queries, 'propertytype'),
            $this->distinctListingValues($queries, 'state'),
        ];
    }

    private function distinctListingValues(array $queries, string $column): Collection
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

    private function resolveDetailSource(?string $source): string
    {
        return strtolower(trim((string) $source)) === 'icp' ? 'icp' : 'ipp';
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

    private function canManageSource(string $source): bool
    {
        return $source === 'ipp';
    }

    private function decorateListing(Listing $listing, string $source, bool $canManage, string $returnSource): Listing
    {
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
                fn (Listing $listing) => $this->normalizedSortValue($listing, $sortBy),
                SORT_NATURAL,
                $sortDir === 'desc'
            )
            ->values();
    }

    private function normalizedSortValue(Listing $listing, string $sortBy): string|float
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

    private function validateListing(Request $request): array
    {
        $validated = $request->validate([
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
            'new_images.*' => ['file', 'image', 'max:10240'],
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

        return $validated;
    }

    private function formViewData(array $form, ?Listing $listing = null): array
    {
        return [
            'listing' => $listing,
            'form' => $form,
            'states' => $this->resolveStates(Auth::guard('agent')->user()->username),
            'listingTypes' => ListingEditor::listingTypes(),
            'propertyTypes' => ListingEditor::propertyTypes(),
            'generalFieldGroups' => ListingEditor::generalFieldGroups(),
            'generalSectionTitles' => ListingEditor::sectionTitles(),
        ];
    }

    private function persistListing(
        Listing $listing,
        array $validated,
        string $username,
        bool $creating,
        string $propertyId,
        array $uploadedPhotoPaths = []
    ): array {
        $timestamp = Carbon::now()->format('YmdHis');
        $currentPhotoPaths = $creating ? [] : ListingEditor::photoPaths($listing);
        $retainedPhotoPaths = $creating
            ? []
            : array_values(array_intersect(
                $currentPhotoPaths,
                ListingEditor::normalizePhotoPaths($validated['existing_photos'] ?? [])
            ));
        $photoPaths = ListingEditor::mergePhotoPaths($retainedPhotoPaths, $uploadedPhotoPaths);
        $removedPhotoPaths = $creating
            ? []
            : array_values(array_diff($currentPhotoPaths, $photoPaths));

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
            $attributes['createddate'] = $timestamp;
        }

        $listing->fill($attributes);
        $listing->save();

        foreach (ListingEditor::detailPayload($validated, $photoPaths, $creating ? null : $listing) as $key => $value) {
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

        return $removedPhotoPaths;
    }

    private function generatePropertyId(): string
    {
        $base = Carbon::now()->format('YmdHis');
        $propertyId = $base;
        $suffix = 0;

        while (Listing::query()->where('propertyid', $propertyId)->exists()) {
            $suffix++;
            $propertyId = $base . sprintf('%02d', $suffix);
        }

        return $propertyId;
    }

    private function archiveListing(Listing $listing): void
    {
        $deletedDate = Carbon::now()->format('YmdHis');
        $connection = DB::connection('mysql2');

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

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    private function storeUploadedImages(string $username, string $propertyId, array $files): array
    {
        $storedPaths = [];
        $directory = 'listings/' . trim($username) . '/' . trim($propertyId);

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $filename = sprintf(
                '%s-%s.%s',
                str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                Str::random(18),
                $extension
            );

            $storedPaths[] = Storage::disk('public')->putFileAs($directory, $file, $filename);
        }

        return $storedPaths;
    }

    private function deleteLocalPhotos(array $paths): void
    {
        foreach (array_unique(ListingEditor::normalizePhotoPaths($paths)) as $path) {
            $storagePath = ListingEditor::localStoragePhotoPath($path);

            if ($storagePath) {
                Storage::disk('public')->delete($storagePath);
            }
        }
    }
}
