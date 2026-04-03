<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\State;
use App\Support\ListingEditor;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $query = $this->ownedListingsQuery($username);

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
            $query->where(function ($q) use ($request) {
                $q->where('propertyname', 'like', '%' . $request->search . '%')
                  ->orWhere('area', 'like', '%' . $request->search . '%');
            });
        }

        $allowedSorts = ['createddate', 'updateddate', 'price', 'propertyname', 'listingtype', 'propertytype', 'state', 'area'];
        $sortBy = $request->get('sort', 'createddate');
        $sortBy = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'createddate';

        $sortDir = strtolower($request->get('dir', 'desc'));
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $query->orderBy($sortBy, $sortDir);

        $listings = $query->paginate(12);

        $listingTypes = $this->ownedListingsQuery($username)->distinct()->pluck('listingtype')->filter();
        $propertyTypes = $this->ownedListingsQuery($username)->distinct()->pluck('propertytype')->filter();
        $states = $this->resolveStates($username);

        return view('listings.index', compact('listings', 'listingTypes', 'propertyTypes', 'states'));
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

    public function show($id)
    {
        $listing = $this->findOwnedListingOrFail($id, true);

        return view('listings.show', compact('listing'));
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

    private function findOwnedListingOrFail(int|string $id, bool $withRelations = false): Listing
    {
        $query = Listing::query()->active();

        if ($withRelations) {
            $query->with(['details', 'agent.detail']);
        }

        $listing = $query->findOrFail($id);

        if ($listing->username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }

        return $listing;
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
    ): array
    {
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
