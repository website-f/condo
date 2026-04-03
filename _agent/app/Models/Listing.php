<?php

namespace App\Models;

use App\Support\SharedAssetUrl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Listing extends Model
{
    private const GENERAL_EXCLUDED_KEYS = [
        'PropertyNameMudah',
        'PropertyNamePropertyGuru',
        'PropertyGuruHeadline',
        'PropertyGuruPlusCode',
        'PropertyGuruDefaultPhoto',
        'PropertyNameMudah_2',
        'Descriptions_2',
        'PropertyNameiProperty',
        'PropertyNameiProperty_2',
        'PropwallPropertyName',
        'Remarks',
        'PropertyNameTheEdgeProperty',
        'PropertyNameDurianProperty',
        'PropertyNamePropsocial',
        'WatermarkText',
    ];

    private const GENERAL_LABELS = [
        'BumiputeraLot' => 'Bumiputera Lot',
        'TitleType' => 'Title Type',
        'LandTitleType' => 'Land Title Type',
        'NumOfBedrooms' => 'Bedrooms',
        'NumOfBathrooms' => 'Bathrooms',
        'UnitType' => 'Unit Type',
        'CarPark' => 'Car Park',
        'RentDeposit' => 'Rent Deposit',
        'RentalLease' => 'Rental Lease',
        'LandArea' => 'Land Area',
        'NewLaunch_NumUnitLots' => 'New Launch Units',
        'Auction_Date' => 'Auction Date',
        'BuiltUpArea' => 'Built Up Area',
        'Auction_Number' => 'Auction Number',
    ];

    protected $connection = 'mysql2';
    protected $table = 'Posts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $appends = ['image_url', 'gallery_images'];

    protected $fillable = [
        'id', 'username', 'propertyid', 'propertyname', 'propertytype',
        'listingtype', 'price', 'state', 'area', 'keywords',
        'totalphoto', 'photopath', 'cobroke', 'createddate',
        'updateddate', 'isDeleted',
    ];

    protected $casts = [
        'totalphoto' => 'integer',
        'cobroke' => 'integer',
        'isDeleted' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('isDeleted', 0);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ListingDetail::class, 'postid', 'id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'username', 'username');
    }

    public function getMetaValue(string $key): ?string
    {
        $detail = $this->detailCollection()->firstWhere('meta_key', $key);
        return $detail?->meta_value;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'RM ' . number_format((float) $this->price, 0, '.', ',');
    }

    public function getPhotopathAttribute($value): ?string
    {
        return SharedAssetUrl::listing(
            $this->attributes['username'] ?? null,
            $this->attributes['propertyid'] ?? null,
            $value
        );
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->photopath;
    }

    public function getGalleryImagesAttribute(): array
    {
        $photoRow = $this->detailCollection()->firstWhere('meta_key', 'Photos');
        $rawPhotos = array_filter(array_map('trim', explode('::', (string) $photoRow?->meta_value)));
        $images = [];

        foreach ($rawPhotos as $photo) {
            $url = SharedAssetUrl::listing(
                $this->attributes['username'] ?? null,
                $this->attributes['propertyid'] ?? null,
                $photo
            );

            if ($url && ! in_array($url, $images, true)) {
                $images[] = $url;
            }
        }

        if ($images === [] && $this->photopath) {
            $images[] = $this->photopath;
        }

        return $images;
    }

    public function getDescriptionTextAttribute(): ?string
    {
        $description = trim((string) $this->getMetaValue('Descriptions'));

        if ($description === '') {
            return null;
        }

        return str_replace('N/A', '-', $description);
    }

    public function getGeneralDetailsAttribute(): array
    {
        $rawDetails = (string) $this->getMetaValue('General');

        if ($rawDetails === '') {
            return [];
        }

        $pairs = explode('::', str_replace('N/A', '-', $rawDetails));
        $details = [];

        for ($index = 0; $index + 1 < count($pairs); $index += 2) {
            $key = trim($pairs[$index]);
            $value = trim($pairs[$index + 1]);
            $label = $this->normalizeGeneralLabel($key);

            if ($label === null || $value === '') {
                continue;
            }

            $details[$label] = $value;
        }

        return $details;
    }

    public function getFormattedCreatedDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['createddate'] ?? null);
    }

    public function getFormattedUpdatedDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['updateddate'] ?? null);
    }

    public function getLocationLabelAttribute(): string
    {
        return collect([
            $this->attributes['area'] ?? null,
            $this->attributes['state'] ?? null,
        ])->filter()->implode(', ');
    }

    protected function detailCollection(): Collection
    {
        return $this->relationLoaded('details')
            ? $this->details
            : $this->details()->get();
    }

    protected function normalizeGeneralLabel(string $key): ?string
    {
        if ($key === '' || in_array($key, self::GENERAL_EXCLUDED_KEYS, true)) {
            return null;
        }

        return self::GENERAL_LABELS[$key] ?? Str::headline($key);
    }

    protected function formatLegacyDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            if (ctype_digit($value) && strlen($value) === 14) {
                return Carbon::createFromFormat('YmdHis', $value)->format('M d, Y h:i A');
            }

            if (ctype_digit($value) && strlen($value) === 8) {
                return Carbon::createFromFormat('Ymd', $value)->format('M d, Y');
            }

            if (ctype_digit($value) && strlen($value) === 10) {
                return Carbon::createFromTimestamp((int) $value)->format('M d, Y h:i A');
            }
        } catch (\Throwable) {
            return $value;
        }

        return $value;
    }
}
