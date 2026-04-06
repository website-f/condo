<?php

namespace App\Support;

use App\Models\CondoListing;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CondoWordpressBridge
{
    public const META_USERNAME = 'condo_agent_username';
    public const META_PROPERTY_ID = 'condo_propertyid';
    public const META_PROPERTY_TYPE = 'condo_propertytype';
    public const META_LISTING_TYPE = 'condo_listingtype';
    public const META_PRICE = 'condo_price';
    public const META_STATE = 'condo_state';
    public const META_AREA = 'condo_area';
    public const META_KEYWORDS = 'condo_keywords';
    public const META_TOTAL_PHOTO = 'condo_totalphoto';
    public const META_COBROKE = 'condo_cobroke';
    public const META_SOURCE_URL = 'condo_source_url';

    private const UPLOADS_SEGMENT = 'agent-listings';

    /**
     * @return array<int, string>
     */
    public function storeUploadedImages(string $propertyId, array $files, array $existingPhotoPaths = []): array
    {
        $storedUrls = [];
        $directory = $this->absoluteUploadsPath($propertyId);

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return [];
        }

        $sequence = $this->nextUploadSequence($propertyId, $existingPhotoPaths);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            [$payload, $extension] = $this->prepareImagePayload($file);

            if ($payload === '') {
                continue;
            }

            $filename = sprintf('%03d.%s', $sequence, $extension);
            $relativePath = $this->relativeUploadsPath($propertyId, $filename);
            $absolutePath = $this->absoluteUploadsPath($propertyId, $filename);

            file_put_contents($absolutePath, $payload);
            $storedUrls[] = self::publicUrlForRelativeUploadPath($relativePath);
            $sequence++;
        }

        return $storedUrls;
    }

    public function deleteStoredImages(array $paths): void
    {
        foreach (array_unique(ListingEditor::normalizePhotoPaths($paths)) as $path) {
            $relativePath = self::relativeUploadPathFromUrl($path);

            if ($relativePath === null || ! str_starts_with($relativePath, self::UPLOADS_SEGMENT . '/')) {
                continue;
            }

            $absolutePath = $this->absoluteUploadsPath(null, $relativePath);

            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }
    }

    public function syncListing(CondoListing $listing, array $validated, string $username, string $propertyId, array $photoPaths): void
    {
        $photoUrls = collect(ListingEditor::normalizePhotoPaths($photoPaths))
            ->map(fn (string $path) => self::normalizePhotoUrl($username, $propertyId, $path))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $listingId = (int) $listing->getKey();
        $description = trim((string) ($validated['description'] ?? ''));
        $keywords = ListingEditor::buildKeywords($validated);
        $excerpt = trim(Str::limit($description !== '' ? $description : $keywords, 180, ''));

        DB::connection('condo')->table('posts')
            ->where('ID', $listingId)
            ->update([
                'post_author' => $this->resolveAuthorId($username),
                'post_content' => $description,
                'post_excerpt' => $excerpt,
                'post_name' => $this->buildListingSlug((string) $validated['propertyname'], $propertyId),
                'post_status' => 'publish',
                'post_type' => 'properties',
                'guid' => rtrim(self::siteBaseUrl(), '/') . '/?post_type=properties&p=' . $listingId,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ]);

        $this->upsertSingleMeta($listingId, 'Descriptions', $description);
        $this->upsertSingleMeta($listingId, 'Photos', implode('::', $photoUrls));
        $this->upsertSingleMeta($listingId, self::META_USERNAME, $username);
        $this->upsertSingleMeta($listingId, self::META_PROPERTY_ID, $propertyId);
        $this->upsertSingleMeta($listingId, self::META_PROPERTY_TYPE, trim((string) $validated['propertytype']));
        $this->upsertSingleMeta($listingId, self::META_LISTING_TYPE, trim((string) $validated['listingtype']));
        $this->upsertSingleMeta($listingId, self::META_PRICE, trim((string) $validated['price']));
        $this->upsertSingleMeta($listingId, self::META_STATE, trim((string) $validated['state']));
        $this->upsertSingleMeta($listingId, self::META_AREA, trim((string) $validated['area']));
        $this->upsertSingleMeta($listingId, self::META_KEYWORDS, $keywords);
        $this->upsertSingleMeta($listingId, self::META_TOTAL_PHOTO, (string) count($photoUrls));
        $this->upsertSingleMeta($listingId, self::META_COBROKE, (string) ((int) ($validated['cobroke'] ?? 0)));

        $this->syncEstatikMeta($listingId, $validated, $keywords, $description);
        $this->syncTaxonomies($listingId, $validated);
        $attachmentIds = $this->syncGalleryAttachments($listingId, $propertyId, (string) $validated['propertyname'], $photoUrls);
        $this->syncGalleryMeta($listingId, $attachmentIds);
        app(RankMathBridge::class)->syncDerivedDefaults($listing->fresh('details'));
    }

    public function deleteListingAssets(CondoListing $listing): void
    {
        foreach ($this->galleryAttachmentIds((int) $listing->getKey()) as $attachmentId) {
            $this->deleteAttachment($attachmentId);
        }
    }

    public function propertyIdExists(string $propertyId): bool
    {
        return DB::connection('condo')
            ->table('postmeta as meta')
            ->join('posts as posts', 'posts.ID', '=', 'meta.post_id')
            ->where('meta.meta_key', self::META_PROPERTY_ID)
            ->where('meta.meta_value', $propertyId)
            ->where('posts.post_type', 'properties')
            ->whereNotIn('posts.post_status', ['trash', 'auto-draft', 'inherit'])
            ->exists();
    }

    public static function siteBaseUrl(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '') {
            return preg_replace('#/agent/?$#i', '', $appUrl) ?: $appUrl;
        }

        try {
            $home = DB::connection('condo')->table('options')->where('option_name', 'home')->value('option_value');

            return rtrim((string) $home, '/');
        } catch (\Throwable) {
            return '';
        }
    }

    public static function publicUrlForRelativeUploadPath(string $relativePath): string
    {
        return rtrim(self::siteBaseUrl(), '/') . '/wp-content/uploads/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    }

    public static function relativeUploadPathFromUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (preg_match('#^https?://#i', $path) !== 1) {
            $normalized = ltrim(str_replace('\\', '/', $path), '/');

            if (str_starts_with($normalized, self::UPLOADS_SEGMENT . '/')) {
                return $normalized;
            }

            if (str_starts_with($normalized, 'wp-content/uploads/')) {
                return substr($normalized, 19);
            }

            return null;
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);

        if (! is_string($parsedPath) || $parsedPath === '') {
            return null;
        }

        $parsedPath = str_replace('\\', '/', $parsedPath);

        if (preg_match('#/wp-content/uploads/(.+)$#i', $parsedPath, $matches) === 1) {
            return ltrim($matches[1], '/');
        }

        return null;
    }

    public static function normalizePhotoUrl(?string $username, string|int|null $propertyId, ?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $relativeUploadPath = self::relativeUploadPathFromUrl($path);

        if ($relativeUploadPath !== null) {
            return self::publicUrlForRelativeUploadPath($relativeUploadPath);
        }

        return SharedAssetUrl::listing($username, $propertyId, $path)
            ?? SharedAssetUrl::storage($path);
    }

    private function syncEstatikMeta(int $listingId, array $validated, string $keywords, string $description): void
    {
        $meta = [
            'es_property_price' => trim((string) ($validated['price'] ?? '')),
            'es_property_bedrooms' => trim((string) ($validated['bedrooms'] ?? '')),
            'es_property_bathrooms' => trim((string) ($validated['bathrooms'] ?? '')),
            'es_property_area' => trim((string) ($validated['built_up_area'] ?? '')),
            'es_property_lot_size' => trim((string) ($validated['land_area'] ?? '')),
            'es_property_year_built' => trim((string) ($validated['year_built'] ?? '')),
            'es_property_floor_level' => trim((string) ($validated['floor'] ?? '')),
            'es_property_price_note' => trim((string) ($validated['negotiable'] ?? '')),
            'es_property_address' => trim((string) ($validated['address'] ?? '')),
            'es_property_keywords' => $keywords,
            'es_property_postal_code' => trim((string) ($validated['postcode'] ?? '')),
            'es_property_is_manual_address' => '1',
            'condo_township' => trim((string) ($validated['township'] ?? '')),
            'condo_tenure' => trim((string) ($validated['tenure'] ?? '')),
            'condo_title_type' => trim((string) ($validated['title_type'] ?? '')),
            'condo_land_title_type' => trim((string) ($validated['land_title_type'] ?? '')),
            'condo_occupancy' => trim((string) ($validated['occupancy'] ?? '')),
            'condo_unit_type' => trim((string) ($validated['unit_type'] ?? '')),
            'condo_bumiputera_lot' => trim((string) ($validated['bumiputera_lot'] ?? '')),
            'condo_negotiable' => trim((string) ($validated['negotiable'] ?? '')),
            'condo_car_park' => trim((string) ($validated['car_park'] ?? '')),
            'condo_furnishing' => trim((string) ($validated['furnishing'] ?? '')),
            'condo_rent_deposit' => trim((string) ($validated['rent_deposit'] ?? '')),
            'condo_auction_date' => trim((string) ($validated['auction_date'] ?? '')),
            'condo_auction_number' => trim((string) ($validated['auction_number'] ?? '')),
            'condo_new_launch_units' => trim((string) ($validated['new_launch_units'] ?? '')),
            'condo_features' => trim((string) ($validated['features'] ?? '')),
            'condo_listing_summary' => trim(Str::limit($description !== '' ? $description : $keywords, 180, '')),
        ];

        foreach ($meta as $key => $value) {
            $this->upsertSingleMeta($listingId, $key, $value);
        }
    }

    private function syncTaxonomies(int $listingId, array $validated): void
    {
        $categoryName = trim((string) ($validated['listingtype'] ?? '')) === 'Rent' ? 'For rent' : 'For sale';
        $typeName = trim((string) ($validated['propertytype'] ?? ''));
        $statusName = 'Active';

        $this->replaceTaxonomyTerms($listingId, 'es_category', [$categoryName]);
        $this->replaceTaxonomyTerms($listingId, 'es_type', $typeName !== '' ? [$typeName] : []);
        $this->replaceTaxonomyTerms($listingId, 'es_status', [$statusName]);
    }

    /**
     * @param  array<int, string>  $photoUrls
     * @return array<int, int>
     */
    private function syncGalleryAttachments(int $listingId, string $propertyId, string $propertyName, array $photoUrls): array
    {
        $attachmentIds = [];

        foreach ($photoUrls as $index => $photoUrl) {
            $attachmentIds[] = $this->ensureAttachmentForPhoto($listingId, $propertyId, $propertyName, $photoUrl, $index);
        }

        $attachmentIds = array_values(array_filter($attachmentIds));

        foreach ($this->galleryAttachmentIds($listingId) as $existingAttachmentId) {
            if (! in_array($existingAttachmentId, $attachmentIds, true)) {
                $this->deleteAttachment($existingAttachmentId);
            }
        }

        foreach ($attachmentIds as $index => $attachmentId) {
            DB::connection('condo')->table('posts')
                ->where('ID', $attachmentId)
                ->update([
                    'post_parent' => $listingId,
                    'post_status' => 'inherit',
                ]);

            $this->upsertSingleMeta($attachmentId, 'es_attachment_order', (string) $index);
            $this->upsertSingleMeta($attachmentId, 'es_attachment_type', 'gallery');
        }

        return $attachmentIds;
    }

    /**
     * @param  array<int, int>  $attachmentIds
     */
    private function syncGalleryMeta(int $listingId, array $attachmentIds): void
    {
        DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $listingId)
            ->whereIn('meta_key', ['es_property_gallery', '_thumbnail_id'])
            ->delete();

        foreach ($attachmentIds as $attachmentId) {
            DB::connection('condo')->table('postmeta')->insert([
                'post_id' => $listingId,
                'meta_key' => 'es_property_gallery',
                'meta_value' => (string) $attachmentId,
            ]);
        }

        if ($attachmentIds !== []) {
            DB::connection('condo')->table('postmeta')->insert([
                'post_id' => $listingId,
                'meta_key' => '_thumbnail_id',
                'meta_value' => (string) $attachmentIds[0],
            ]);
        }
    }

    private function ensureAttachmentForPhoto(int $listingId, string $propertyId, string $propertyName, string $photoUrl, int $order): ?int
    {
        $existingAttachmentId = $this->findAttachmentIdByPhoto($listingId, $photoUrl);

        if ($existingAttachmentId !== null) {
            return $existingAttachmentId;
        }

        $relativePath = self::relativeUploadPathFromUrl($photoUrl);

        if ($relativePath === null) {
            $relativePath = $this->mirrorPhotoIntoUploads($propertyId, $photoUrl, $order);
        }

        if ($relativePath === null) {
            return null;
        }

        return $this->createAttachment($listingId, $propertyName, $relativePath, $photoUrl);
    }

    private function findAttachmentIdByPhoto(int $listingId, string $photoUrl): ?int
    {
        $relativePath = self::relativeUploadPathFromUrl($photoUrl);

        $query = DB::connection('condo')
            ->table('posts as posts')
            ->leftJoin('postmeta as source_meta', function ($join) {
                $join->on('source_meta.post_id', '=', 'posts.ID')
                    ->where('source_meta.meta_key', '=', self::META_SOURCE_URL);
            })
            ->leftJoin('postmeta as file_meta', function ($join) {
                $join->on('file_meta.post_id', '=', 'posts.ID')
                    ->where('file_meta.meta_key', '=', '_wp_attached_file');
            })
            ->where('posts.post_type', 'attachment')
            ->where(function ($builder) use ($listingId) {
                $builder->where('posts.post_parent', $listingId)
                    ->orWhere('posts.post_parent', 0);
            })
            ->where(function ($builder) use ($photoUrl, $relativePath) {
                $builder->where('source_meta.meta_value', $photoUrl);

                if ($relativePath !== null) {
                    $builder->orWhere('file_meta.meta_value', $relativePath);
                }
            })
            ->orderByDesc('posts.post_parent')
            ->value('posts.ID');

        return $query ? (int) $query : null;
    }

    private function createAttachment(int $listingId, string $propertyName, string $relativePath, string $photoUrl): int
    {
        $absolutePath = $this->absoluteUploadsPath(null, $relativePath);
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
        $mimeType = is_file($absolutePath) ? (mime_content_type($absolutePath) ?: ('image/' . ($extension === 'jpg' ? 'jpeg' : $extension))) : '';
        $now = Carbon::now();

        $attachmentId = DB::connection('condo')->table('posts')->insertGetId([
            'post_author' => 1,
            'post_date' => $now->format('Y-m-d H:i:s'),
            'post_date_gmt' => $now->clone()->utc()->format('Y-m-d H:i:s'),
            'post_content' => '',
            'post_title' => trim($propertyName) !== '' ? trim($propertyName) . ' ' . $filename : $filename,
            'post_excerpt' => '',
            'post_status' => 'inherit',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => Str::slug($filename),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now->format('Y-m-d H:i:s'),
            'post_modified_gmt' => $now->clone()->utc()->format('Y-m-d H:i:s'),
            'post_content_filtered' => '',
            'post_parent' => $listingId,
            'guid' => self::publicUrlForRelativeUploadPath($relativePath),
            'menu_order' => 0,
            'post_type' => 'attachment',
            'post_mime_type' => $mimeType,
            'comment_count' => 0,
        ]);

        DB::connection('condo')->table('postmeta')->insert([
            [
                'post_id' => $attachmentId,
                'meta_key' => '_wp_attached_file',
                'meta_value' => $relativePath,
            ],
            [
                'post_id' => $attachmentId,
                'meta_key' => self::META_SOURCE_URL,
                'meta_value' => $photoUrl,
            ],
        ]);

        return (int) $attachmentId;
    }

    private function mirrorPhotoIntoUploads(string $propertyId, string $photoUrl, int $order): ?string
    {
        $sourceStoragePath = $this->sourceLaravelStoragePath($photoUrl);
        $extension = strtolower((string) pathinfo(parse_url($photoUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        $extension = $extension === '' ? 'jpg' : ($extension === 'jpeg' ? 'jpg' : $extension);
        $targetFilename = sprintf('%03d.%s', $this->nextUploadSequence($propertyId), $extension);
        $relativePath = $this->relativeUploadsPath($propertyId, $targetFilename);
        $absolutePath = $this->absoluteUploadsPath($propertyId, $targetFilename);
        $directory = dirname($absolutePath);

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return null;
        }

        if ($sourceStoragePath !== null && is_file($sourceStoragePath)) {
            copy($sourceStoragePath, $absolutePath);

            return $relativePath;
        }

        $response = Http::timeout(20)
            ->retry(1, 200)
            ->withHeaders(['User-Agent' => 'PropertyAgent CMS'])
            ->get($photoUrl);

        if (! $response->successful()) {
            return null;
        }

        file_put_contents($absolutePath, $response->body());

        return $relativePath;
    }

    /**
     * @return array<int, int>
     */
    private function galleryAttachmentIds(int $listingId): array
    {
        return DB::connection('condo')
            ->table('posts as posts')
            ->leftJoin('postmeta as attachment_type', function ($join) {
                $join->on('attachment_type.post_id', '=', 'posts.ID')
                    ->where('attachment_type.meta_key', '=', 'es_attachment_type');
            })
            ->where('posts.post_type', 'attachment')
            ->where('posts.post_parent', $listingId)
            ->where(function ($query) {
                $query->where('attachment_type.meta_value', 'gallery')
                    ->orWhereNull('attachment_type.meta_value');
            })
            ->orderBy('posts.ID')
            ->pluck('posts.ID')
            ->map(fn (mixed $id) => (int) $id)
            ->all();
    }

    private function deleteAttachment(int $attachmentId): void
    {
        $relativePath = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $attachmentId)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        if (is_string($relativePath) && $relativePath !== '') {
            $absolutePath = $this->absoluteUploadsPath(null, $relativePath);

            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }

        DB::connection('condo')->table('postmeta')->where('post_id', $attachmentId)->delete();
        DB::connection('condo')->table('posts')->where('ID', $attachmentId)->delete();
    }

    /**
     * @param  array<int, string>  $termNames
     */
    private function replaceTaxonomyTerms(int $listingId, string $taxonomy, array $termNames): void
    {
        $currentTermTaxonomyIds = DB::connection('condo')
            ->table('term_relationships as relationships')
            ->join('term_taxonomy as taxonomy', 'taxonomy.term_taxonomy_id', '=', 'relationships.term_taxonomy_id')
            ->where('relationships.object_id', $listingId)
            ->where('taxonomy.taxonomy', $taxonomy)
            ->pluck('relationships.term_taxonomy_id')
            ->all();

        DB::connection('condo')
            ->table('term_relationships')
            ->where('object_id', $listingId)
            ->whereIn('term_taxonomy_id', $currentTermTaxonomyIds)
            ->delete();

        foreach ($currentTermTaxonomyIds as $termTaxonomyId) {
            $this->refreshTermCount((int) $termTaxonomyId);
        }

        foreach (collect($termNames)->map(fn (string $name) => trim($name))->filter()->unique() as $termName) {
            $termTaxonomyId = $this->ensureTermTaxonomyId($taxonomy, $termName);

            DB::connection('condo')->table('term_relationships')->insert([
                'object_id' => $listingId,
                'term_taxonomy_id' => $termTaxonomyId,
                'term_order' => 0,
            ]);

            $this->refreshTermCount($termTaxonomyId);
        }
    }

    private function ensureTermTaxonomyId(string $taxonomy, string $termName): int
    {
        $slug = Str::slug($termName);

        $existing = DB::connection('condo')
            ->table('terms as terms')
            ->join('term_taxonomy as taxonomy_table', 'taxonomy_table.term_id', '=', 'terms.term_id')
            ->where('taxonomy_table.taxonomy', $taxonomy)
            ->where(function ($query) use ($termName, $slug) {
                $query->where('terms.slug', $slug)
                    ->orWhere('terms.name', $termName);
            })
            ->select('taxonomy_table.term_taxonomy_id')
            ->first();

        if ($existing) {
            return (int) $existing->term_taxonomy_id;
        }

        $termId = DB::connection('condo')->table('terms')->insertGetId([
            'name' => $termName,
            'slug' => $slug,
            'term_group' => 0,
        ]);

        return (int) DB::connection('condo')->table('term_taxonomy')->insertGetId([
            'term_id' => $termId,
            'taxonomy' => $taxonomy,
            'description' => '',
            'parent' => 0,
            'count' => 0,
        ]);
    }

    private function refreshTermCount(int $termTaxonomyId): void
    {
        $count = DB::connection('condo')
            ->table('term_relationships')
            ->where('term_taxonomy_id', $termTaxonomyId)
            ->count();

        DB::connection('condo')
            ->table('term_taxonomy')
            ->where('term_taxonomy_id', $termTaxonomyId)
            ->update(['count' => $count]);
    }

    private function upsertSingleMeta(int $postId, string $metaKey, string $metaValue): void
    {
        $metaValue = trim($metaValue);

        if ($metaValue === '') {
            DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', $metaKey)
                ->delete();

            return;
        }

        $existingMetaId = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $metaKey)
            ->value('meta_id');

        if ($existingMetaId) {
            DB::connection('condo')
                ->table('postmeta')
                ->where('meta_id', $existingMetaId)
                ->update(['meta_value' => $metaValue]);

            DB::connection('condo')
                ->table('postmeta')
                ->where('post_id', $postId)
                ->where('meta_key', $metaKey)
                ->where('meta_id', '!=', $existingMetaId)
                ->delete();

            return;
        }

        DB::connection('condo')->table('postmeta')->insert([
            'post_id' => $postId,
            'meta_key' => $metaKey,
            'meta_value' => $metaValue,
        ]);
    }

    private function resolveAuthorId(string $username): int
    {
        $authorId = DB::connection('condo')
            ->table('users')
            ->where('user_login', $username)
            ->value('ID');

        return $authorId ? (int) $authorId : 1;
    }

    private function buildListingSlug(string $propertyName, string $propertyId): string
    {
        return Str::slug(trim($propertyName) . ' ' . trim($propertyId));
    }

    private function nextUploadSequence(string $propertyId, array $existingPhotoPaths = []): int
    {
        $highestSequence = collect(ListingEditor::normalizePhotoPaths($existingPhotoPaths))
            ->map(function (string $path) {
                $filename = pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_FILENAME);

                return preg_match('/^(\d+)/', (string) $filename, $matches) === 1 ? (int) $matches[1] : 0;
            })
            ->max();

        $directory = $this->absoluteUploadsPath($propertyId);

        if (is_dir($directory)) {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*.*') ?: [] as $filePath) {
                $filename = pathinfo($filePath, PATHINFO_FILENAME);

                if (preg_match('/^(\d+)/', (string) $filename, $matches) === 1) {
                    $highestSequence = max((int) $highestSequence, (int) $matches[1]);
                }
            }
        }

        return max(1, ((int) $highestSequence) + 1);
    }

    private function absoluteUploadsPath(?string $propertyId = null, ?string $suffix = null): string
    {
        $base = dirname(base_path()) . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads';

        if ($suffix !== null) {
            return $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($suffix, '/'));
        }

        if ($propertyId === null || trim($propertyId) === '') {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::UPLOADS_SEGMENT . '/' . trim($propertyId));
    }

    private function relativeUploadsPath(string $propertyId, string $filename): string
    {
        return self::UPLOADS_SEGMENT . '/' . trim($propertyId) . '/' . ltrim($filename, '/');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function prepareImagePayload(UploadedFile $file): array
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

    private function sourceLaravelStoragePath(string $photoUrl): ?string
    {
        $parsedPath = parse_url($photoUrl, PHP_URL_PATH);

        if (! is_string($parsedPath) || $parsedPath === '') {
            return null;
        }

        if (preg_match('#/(?:agent/)?storage/(.+)$#i', $parsedPath, $matches) !== 1) {
            return null;
        }

        return storage_path('app/public/' . str_replace('/', DIRECTORY_SEPARATOR, $matches[1]));
    }
}
