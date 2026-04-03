<?php

namespace App\Support;

use App\Models\Listing;

class ListingEditor
{
    private const LISTING_TYPES = ['Sale', 'Rent', 'Auction', 'NewLaunch'];

    private const PROPERTY_TYPES = [
        'Shop',
        'Condo/Serviced Residence',
        'Agricultural Land',
        'Apartment/Flat',
        'Bungalow',
        'Commercial Land',
        'Factory/Warehouse',
        'Industrial Land',
        'Office',
        'Residential Land',
        'Retail Space',
        'Semi-D',
        'Shop/Office/Retail Space',
        'SOHO',
        'Terrace 1.5 storey',
        'Terrace 2.5 storey',
        'Terrace double storey',
        'Terrace single storey',
        'Terrace triple storey',
        'Townhouse',
    ];

    private const GENERAL_FIELDS = [
        'address' => [
            'raw_key' => 'Address',
            'label' => 'Street Address',
            'section' => 'location',
            'type' => 'textarea',
            'rows' => 3,
            'placeholder' => 'Building, street, or project address',
        ],
        'township' => [
            'raw_key' => 'Township',
            'label' => 'Township',
            'section' => 'location',
            'placeholder' => 'e.g. Mont Kiara',
        ],
        'postcode' => [
            'raw_key' => 'Postcode',
            'label' => 'Postcode',
            'section' => 'location',
            'placeholder' => 'e.g. 50480',
        ],
        'tenure' => [
            'raw_key' => 'Tenure',
            'label' => 'Tenure',
            'section' => 'location',
            'placeholder' => 'e.g. Freehold',
        ],
        'title_type' => [
            'raw_key' => 'TitleType',
            'label' => 'Title Type',
            'section' => 'location',
            'placeholder' => 'e.g. Individual',
        ],
        'land_title_type' => [
            'raw_key' => 'LandTitleType',
            'label' => 'Land Title Type',
            'section' => 'location',
            'placeholder' => 'e.g. Commercial',
        ],
        'occupancy' => [
            'raw_key' => 'Occupancy',
            'label' => 'Occupancy',
            'section' => 'location',
            'placeholder' => 'e.g. Vacant / Tenanted',
        ],
        'unit_type' => [
            'raw_key' => 'UnitType',
            'label' => 'Unit Type',
            'section' => 'location',
            'placeholder' => 'e.g. Corner lot',
        ],
        'bumiputera_lot' => [
            'raw_key' => 'BumiputeraLot',
            'label' => 'Bumiputera Lot',
            'section' => 'location',
            'placeholder' => 'e.g. Yes / No',
        ],
        'negotiable' => [
            'raw_key' => 'Negotiable',
            'label' => 'Negotiable',
            'section' => 'location',
            'placeholder' => 'e.g. Yes / No',
        ],
        'bedrooms' => [
            'raw_key' => 'NumOfBedrooms',
            'label' => 'Bedrooms',
            'section' => 'specs',
            'placeholder' => 'e.g. 4',
        ],
        'bathrooms' => [
            'raw_key' => 'NumOfBathrooms',
            'label' => 'Bathrooms',
            'section' => 'specs',
            'placeholder' => 'e.g. 3',
        ],
        'built_up_area' => [
            'raw_key' => 'BuiltUpArea',
            'label' => 'Built Up Area',
            'section' => 'specs',
            'placeholder' => 'e.g. 1,650 sqft',
        ],
        'land_area' => [
            'raw_key' => 'LandArea',
            'label' => 'Land Area',
            'section' => 'specs',
            'placeholder' => 'e.g. 4,356 sqft',
        ],
        'floor' => [
            'raw_key' => 'Floor',
            'label' => 'Floor',
            'section' => 'specs',
            'placeholder' => 'e.g. High floor',
        ],
        'car_park' => [
            'raw_key' => 'CarPark',
            'label' => 'Car Park',
            'section' => 'specs',
            'placeholder' => 'e.g. 2',
        ],
        'furnishing' => [
            'raw_key' => 'Furnishing',
            'label' => 'Furnishing',
            'section' => 'specs',
            'placeholder' => 'e.g. Fully furnished',
        ],
        'year_built' => [
            'raw_key' => 'YearBuilt',
            'label' => 'Year Built',
            'section' => 'specs',
            'placeholder' => 'e.g. 2019',
        ],
        'rent_deposit' => [
            'raw_key' => 'RentDeposit',
            'label' => 'Rent Deposit',
            'section' => 'commercial',
            'placeholder' => 'e.g. 2 + 1',
        ],
        'auction_date' => [
            'raw_key' => 'Auction_Date',
            'label' => 'Auction Date',
            'section' => 'commercial',
            'placeholder' => 'e.g. 2026-05-15',
        ],
        'auction_number' => [
            'raw_key' => 'Auction_Number',
            'label' => 'Auction Number',
            'section' => 'commercial',
            'placeholder' => 'e.g. 12345',
        ],
        'new_launch_units' => [
            'raw_key' => 'NewLaunch_NumUnitLots',
            'label' => 'New Launch Units',
            'section' => 'commercial',
            'placeholder' => 'e.g. 180',
        ],
        'features' => [
            'raw_key' => 'Features',
            'label' => 'Features',
            'section' => 'commercial',
            'type' => 'textarea',
            'rows' => 4,
            'placeholder' => 'Special features, fit-out, or selling points',
        ],
    ];

    private const SECTION_TITLES = [
        'location' => 'Location & Title',
        'specs' => 'Layout & Specs',
        'commercial' => 'Commercial & Notes',
    ];

    public static function listingTypes(): array
    {
        return self::LISTING_TYPES;
    }

    public static function propertyTypes(): array
    {
        return self::PROPERTY_TYPES;
    }

    public static function sectionTitles(): array
    {
        return self::SECTION_TITLES;
    }

    public static function generalFieldGroups(): array
    {
        $groups = [];

        foreach (self::GENERAL_FIELDS as $name => $field) {
            $groups[$field['section']][] = ['name' => $name] + $field;
        }

        return $groups;
    }

    public static function formData(?Listing $listing = null): array
    {
        $rawGeneral = self::parseGeneralPairs($listing?->getMetaValue('General'));
        $existingPhotos = self::photoPaths($listing);
        $data = [
            'propertyname' => $listing?->propertyname ?? '',
            'propertytype' => $listing?->propertytype ?? '',
            'listingtype' => $listing?->listingtype ?? 'Sale',
            'price' => $listing?->price ?? '',
            'state' => $listing?->state ?? '',
            'area' => $listing?->area ?? '',
            'keywords' => $listing?->getRawOriginal('keywords') ?? '',
            'cobroke' => (int) ($listing?->cobroke ?? 0),
            'description' => $listing?->description_text ?? '',
            'existing_photos' => $existingPhotos,
        ];

        foreach (self::GENERAL_FIELDS as $name => $field) {
            $data[$name] = $rawGeneral[$field['raw_key']] ?? '';
        }

        return $data;
    }

    public static function detailPayload(array $validated, array $photos, ?Listing $listing = null): array
    {
        $rawGeneral = self::parseGeneralPairs($listing?->getMetaValue('General'));

        foreach (self::GENERAL_FIELDS as $name => $field) {
            $value = trim((string) ($validated[$name] ?? ''));

            if ($value === '') {
                unset($rawGeneral[$field['raw_key']]);
                continue;
            }

            $rawGeneral[$field['raw_key']] = $value;
        }

        return [
            'Descriptions' => trim((string) ($validated['description'] ?? '')),
            'Photos' => implode('::', $photos),
            'General' => self::serializeGeneralPairs($rawGeneral),
        ];
    }

    public static function normalizePrice(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d.]/', '', $value);

        if ($normalized === null || $normalized === '' || ! preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
            return null;
        }

        return $normalized;
    }

    public static function buildKeywords(array $validated): string
    {
        $keywords = trim((string) ($validated['keywords'] ?? ''));

        if ($keywords !== '') {
            return $keywords;
        }

        return implode(', ', array_filter([
            trim((string) ($validated['propertyname'] ?? '')),
            trim((string) ($validated['propertytype'] ?? '')),
            trim((string) ($validated['listingtype'] ?? '')),
            trim((string) ($validated['area'] ?? '')),
            trim((string) ($validated['state'] ?? '')),
        ]));
    }

    public static function normalizePhotoPaths(mixed $value): array
    {
        if (is_array($value)) {
            $lines = $value;
        } else {
            $lines = preg_split('/\r\n|\r|\n|::/', trim((string) $value)) ?: [];
        }

        $normalized = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || in_array($line, $normalized, true)) {
                continue;
            }

            $normalized[] = $line;
        }

        return $normalized;
    }

    public static function mergePhotoPaths(array ...$groups): array
    {
        $merged = [];

        foreach ($groups as $group) {
            foreach (self::normalizePhotoPaths($group) as $path) {
                if (! in_array($path, $merged, true)) {
                    $merged[] = $path;
                }
            }
        }

        return $merged;
    }

    public static function photoPaths(?Listing $listing): array
    {
        if (! $listing) {
            return [];
        }

        $photoPaths = self::normalizePhotoPaths($listing->getMetaValue('Photos'));

        if ($photoPaths !== []) {
            return $photoPaths;
        }

        $rawFeatured = trim((string) $listing->getRawOriginal('photopath'));

        return $rawFeatured === '' ? [] : [$rawFeatured];
    }

    public static function localStoragePhotoPath(?string $path): ?string
    {
        return SharedAssetUrl::publicStoragePath($path);
    }

    private static function parseGeneralPairs(?string $value): array
    {
        $pairs = explode('::', (string) $value);
        $details = [];

        for ($index = 0; $index + 1 < count($pairs); $index += 2) {
            $key = trim($pairs[$index]);
            $fieldValue = trim($pairs[$index + 1]);

            if ($key === '') {
                continue;
            }

            $details[$key] = $fieldValue;
        }

        return $details;
    }

    private static function serializeGeneralPairs(array $pairs): string
    {
        $serialized = [];

        foreach ($pairs as $key => $value) {
            $key = trim((string) $key);
            $value = trim((string) $value);

            if ($key === '' || $value === '') {
                continue;
            }

            $serialized[] = $key;
            $serialized[] = $value;
        }

        return implode('::', $serialized);
    }
}
