<?php

namespace App\Models;

use App\Support\CondoWordpressBridge;
use App\Support\ListingEditor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CondoListing extends Model
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

    protected $connection = 'condo';
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    public $timestamps = false;

    protected $appends = ['image_url', 'gallery_images'];

    protected $fillable = [
        'propertyname',
        'propertytype',
        'listingtype',
        'price',
        'state',
        'area',
        'keywords',
        'totalphoto',
        'photopath',
        'cobroke',
        'updateddate',
        'username',
        'propertyid',
        'createddate',
        'isDeleted',
    ];

    /**
     * @var array<string, string>
     */
    protected array $pendingMeta = [];

    protected static function booted(): void
    {
        static::saving(function (self $listing) {
            $now = Carbon::now();

            $listing->attributes['post_type'] = 'properties';
            $listing->attributes['comment_status'] = 'closed';
            $listing->attributes['ping_status'] = 'closed';
            $listing->attributes['post_status'] = $listing->attributes['post_status'] ?? 'publish';
            $listing->attributes['post_author'] = $listing->attributes['post_author'] ?? $listing->resolveAuthorId();
            $listing->attributes['post_content'] = $listing->attributes['post_content'] ?? '';
            $listing->attributes['post_excerpt'] = $listing->attributes['post_excerpt'] ?? '';
            $listing->attributes['guid'] = $listing->attributes['guid'] ?? '';
            $listing->attributes['post_date'] = $listing->attributes['post_date'] ?? $now->format('Y-m-d H:i:s');
            $listing->attributes['post_date_gmt'] = $listing->attributes['post_date_gmt'] ?? $now->clone()->utc()->format('Y-m-d H:i:s');
            $listing->attributes['post_modified'] = $now->format('Y-m-d H:i:s');
            $listing->attributes['post_modified_gmt'] = $now->clone()->utc()->format('Y-m-d H:i:s');
        });

        static::saved(function (self $listing) {
            $listing->syncPendingMeta();
        });
    }

    public function scopeActive($query)
    {
        return $query
            ->where('post_type', 'properties')
            ->whereNotIn('post_status', ['trash', 'auto-draft', 'inherit']);
    }

    public function details(): HasMany
    {
        return $this->hasMany(CondoListingDetail::class, 'post_id', 'ID');
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

    public function getPropertynameAttribute(): string
    {
        return trim((string) ($this->attributes['post_title'] ?? ''));
    }

    public function setPropertynameAttribute(mixed $value): void
    {
        $this->attributes['post_title'] = trim((string) $value);
    }

    public function getPropertytypeAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_PROPERTY_TYPE] ?? $this->getMetaValue(CondoWordpressBridge::META_PROPERTY_TYPE)));
    }

    public function setPropertytypeAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_PROPERTY_TYPE, trim((string) $value));
    }

    public function getListingtypeAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_LISTING_TYPE] ?? $this->getMetaValue(CondoWordpressBridge::META_LISTING_TYPE)));
    }

    public function setListingtypeAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_LISTING_TYPE, trim((string) $value));
    }

    public function getPriceAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_PRICE] ?? $this->getMetaValue(CondoWordpressBridge::META_PRICE)));
    }

    public function setPriceAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_PRICE, trim((string) $value));
    }

    public function getStateAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_STATE] ?? $this->getMetaValue(CondoWordpressBridge::META_STATE)));
    }

    public function setStateAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_STATE, trim((string) $value));
    }

    public function getAreaAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_AREA] ?? $this->getMetaValue(CondoWordpressBridge::META_AREA)));
    }

    public function setAreaAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_AREA, trim((string) $value));
    }

    public function getKeywordsAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_KEYWORDS] ?? $this->getMetaValue(CondoWordpressBridge::META_KEYWORDS)));
    }

    public function setKeywordsAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_KEYWORDS, trim((string) $value));
    }

    public function getUsernameAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_USERNAME] ?? $this->getMetaValue(CondoWordpressBridge::META_USERNAME)));
    }

    public function setUsernameAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_USERNAME, trim((string) $value));
    }

    public function getPropertyidAttribute(): string
    {
        return trim((string) ($this->pendingMeta[CondoWordpressBridge::META_PROPERTY_ID] ?? $this->getMetaValue(CondoWordpressBridge::META_PROPERTY_ID)));
    }

    public function setPropertyidAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_PROPERTY_ID, trim((string) $value));
    }

    public function getTotalphotoAttribute(): int
    {
        $value = $this->pendingMeta[CondoWordpressBridge::META_TOTAL_PHOTO] ?? $this->getMetaValue(CondoWordpressBridge::META_TOTAL_PHOTO);

        return is_numeric($value) ? (int) $value : count($this->gallery_images);
    }

    public function setTotalphotoAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_TOTAL_PHOTO, (string) ((int) $value));
    }

    public function getCobrokeAttribute(): int
    {
        return (int) (($this->pendingMeta[CondoWordpressBridge::META_COBROKE] ?? $this->getMetaValue(CondoWordpressBridge::META_COBROKE)) ?? 0);
    }

    public function setCobrokeAttribute(mixed $value): void
    {
        $this->setPendingMeta(CondoWordpressBridge::META_COBROKE, (string) ((int) $value));
    }

    public function getPhotopathAttribute($value): ?string
    {
        return $this->gallery_images[0] ?? null;
    }

    public function setPhotopathAttribute(mixed $value): void
    {
        // WordPress properties derive the cover image from the synced gallery.
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->photopath;
    }

    public function getGalleryImagesAttribute(): array
    {
        $photoPaths = ListingEditor::normalizePhotoPaths($this->getMetaValue('Photos'));
        $images = [];

        foreach ($photoPaths as $path) {
            $url = CondoWordpressBridge::normalizePhotoUrl($this->username, $this->propertyid, $path);

            if ($url && ! in_array($url, $images, true)) {
                $images[] = $url;
            }
        }

        return $images;
    }

    public function getDescriptionTextAttribute(): ?string
    {
        $description = trim((string) ($this->getMetaValue('Descriptions') ?: ($this->attributes['post_content'] ?? '')));

        return $description === '' ? null : $description;
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

    public function getFormattedPriceAttribute(): string
    {
        return 'RM ' . number_format((float) $this->price, 0, '.', ',');
    }

    public function getCreateddateAttribute(): ?string
    {
        return $this->normalizeWordpressDateToLegacy($this->attributes['post_date'] ?? null);
    }

    public function setCreateddateAttribute(mixed $value): void
    {
        [$localDate, $gmtDate] = $this->legacyDateToWordpressPair($value);
        $this->attributes['post_date'] = $localDate;
        $this->attributes['post_date_gmt'] = $gmtDate;
    }

    public function getUpdateddateAttribute(): ?string
    {
        return $this->normalizeWordpressDateToLegacy($this->attributes['post_modified'] ?? null);
    }

    public function setUpdateddateAttribute(mixed $value): void
    {
        [$localDate, $gmtDate] = $this->legacyDateToWordpressPair($value);
        $this->attributes['post_modified'] = $localDate;
        $this->attributes['post_modified_gmt'] = $gmtDate;
    }

    public function getFormattedCreatedDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->createddate);
    }

    public function getFormattedUpdatedDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->updateddate);
    }

    public function getLocationLabelAttribute(): string
    {
        return collect([
            $this->area,
            $this->state,
        ])->filter()->implode(', ');
    }

    public function getIsDeletedAttribute(): bool
    {
        return in_array((string) ($this->attributes['post_status'] ?? ''), ['trash', 'auto-draft'], true);
    }

    public function setIsDeletedAttribute(mixed $value): void
    {
        $this->attributes['post_status'] = ((int) $value) === 1 ? 'trash' : 'publish';
    }

    public static function schemaAvailable(): bool
    {
        try {
            $schema = DB::connection('condo')->getSchemaBuilder();

            return $schema->hasTable('posts')
                && $schema->hasTable('postmeta')
                && $schema->hasTable('terms')
                && $schema->hasTable('term_taxonomy')
                && $schema->hasTable('term_relationships')
                && $schema->hasTable('users');
        } catch (\Throwable) {
            return false;
        }
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

    private function setPendingMeta(string $key, string $value): void
    {
        $this->pendingMeta[$key] = $value;
        $this->unsetRelation('details');
    }

    private function syncPendingMeta(): void
    {
        if (! $this->exists || $this->pendingMeta === []) {
            return;
        }

        foreach ($this->pendingMeta as $key => $value) {
            $query = DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $this->getKey())
                ->where('meta_key', $key);

            if ($value === '') {
                $query->delete();
                continue;
            }

            $metaId = $query->value('meta_id');

            if ($metaId) {
                DB::connection('condo')
                    ->table('postmeta')
                    ->where('meta_id', $metaId)
                    ->update(['meta_value' => $value]);

                DB::connection('condo')
                    ->table('postmeta')
                    ->where('post_id', $this->getKey())
                    ->where('meta_key', $key)
                    ->where('meta_id', '!=', $metaId)
                    ->delete();

                continue;
            }

            DB::connection('condo')->table('postmeta')->insert([
                'post_id' => $this->getKey(),
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
        }

        $this->pendingMeta = [];
        $this->unsetRelation('details');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function legacyDateToWordpressPair(mixed $value): array
    {
        $value = trim((string) $value);

        if ($value !== '' && preg_match('/^\d{14}$/', $value) === 1) {
            $local = Carbon::createFromFormat('YmdHis', $value, config('app.timezone', 'Asia/Kuala_Lumpur'));

            return [
                $local->format('Y-m-d H:i:s'),
                $local->clone()->utc()->format('Y-m-d H:i:s'),
            ];
        }

        $now = Carbon::now();

        return [
            $now->format('Y-m-d H:i:s'),
            $now->clone()->utc()->format('Y-m-d H:i:s'),
        ];
    }

    private function normalizeWordpressDateToLegacy(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone', 'Asia/Kuala_Lumpur'))->format('YmdHis');
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveAuthorId(): int
    {
        $username = trim((string) ($this->pendingMeta[CondoWordpressBridge::META_USERNAME] ?? ''));

        if ($username === '') {
            return 1;
        }

        $authorId = DB::connection('condo')
            ->table('users')
            ->where('user_login', $username)
            ->value('ID');

        return $authorId ? (int) $authorId : 1;
    }
}
