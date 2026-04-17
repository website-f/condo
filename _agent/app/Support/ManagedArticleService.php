<?php

namespace App\Support;

use App\Models\ManagedArticle;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManagedArticleService
{
    private const UPLOADS_SEGMENT = 'agent-articles';

    public function queryForAgent(string $username): Builder
    {
        return ManagedArticle::query()
            ->manageable()
            ->ownedByAgent($username);
    }

    public function trashedQueryForAgent(string $username): Builder
    {
        return ManagedArticle::query()->trashedForAgent($username);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForAgent(string $username, array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->queryForAgent($username);
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('post_title', 'like', '%' . $search . '%')
                    ->orWhere('post_excerpt', 'like', '%' . $search . '%')
                    ->orWhere('post_content', 'like', '%' . $search . '%')
                    ->orWhere('post_name', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '') {
            $query->where('post_status', $status === 'schedule' ? 'future' : $status);
        }

        return $query
            ->orderByDesc('post_modified')
            ->orderByDesc('ID')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{total:int,published:int,scheduled:int,drafts:int}
     */
    public function statsForAgent(string $username): array
    {
        $baseQuery = $this->queryForAgent($username);

        return [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('post_status', 'publish')->count(),
            'scheduled' => (clone $baseQuery)->where('post_status', 'future')->count(),
            'drafts' => (clone $baseQuery)->where('post_status', 'draft')->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(string $username, array $validated): ManagedArticle
    {
        return DB::connection('condo')->transaction(function () use ($username, $validated) {
            [$status, $publishAt] = $this->resolveStatusAndDate($validated);
            $slug = $this->uniqueSlug((string) ($validated['slug'] ?? ''), (string) ($validated['title'] ?? ''));
            $now = now();

            $articleId = DB::connection('condo')->table('posts')->insertGetId([
                'post_author' => $this->resolveAuthorId($username),
                'post_date' => $publishAt->format('Y-m-d H:i:s'),
                'post_date_gmt' => $publishAt->copy()->utc()->format('Y-m-d H:i:s'),
                'post_content' => trim((string) ($validated['content'] ?? '')),
                'post_title' => trim((string) ($validated['title'] ?? '')),
                'post_excerpt' => trim((string) ($validated['excerpt'] ?? '')),
                'post_status' => $status,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => $slug,
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => $now->format('Y-m-d H:i:s'),
                'post_modified_gmt' => $now->copy()->utc()->format('Y-m-d H:i:s'),
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => '',
                'menu_order' => 0,
                'post_type' => 'post',
                'post_mime_type' => '',
                'comment_count' => 0,
            ]);

            DB::connection('condo')
                ->table('posts')
                ->where('ID', $articleId)
                ->update(['guid' => $this->permalinkForId($articleId)]);

            $this->syncArticleDetails($articleId, $username, $validated);

            /** @var ManagedArticle $article */
            $article = ManagedArticle::query()->findOrFail($articleId);

            return $article;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(ManagedArticle $article, string $username, array $validated): ManagedArticle
    {
        return DB::connection('condo')->transaction(function () use ($article, $username, $validated) {
            [$status, $publishAt] = $this->resolveStatusAndDate($validated, $article);
            $slug = $this->uniqueSlug((string) ($validated['slug'] ?? ''), (string) ($validated['title'] ?? ''), (int) $article->getKey());
            $now = now();

            DB::connection('condo')->table('posts')
                ->where('ID', $article->getKey())
                ->update([
                    'post_author' => $this->resolveAuthorId($username),
                    'post_date' => $publishAt->format('Y-m-d H:i:s'),
                    'post_date_gmt' => $publishAt->copy()->utc()->format('Y-m-d H:i:s'),
                    'post_content' => trim((string) ($validated['content'] ?? '')),
                    'post_title' => trim((string) ($validated['title'] ?? '')),
                    'post_excerpt' => trim((string) ($validated['excerpt'] ?? '')),
                    'post_status' => $status,
                    'post_name' => $slug,
                    'post_modified' => $now->format('Y-m-d H:i:s'),
                    'post_modified_gmt' => $now->copy()->utc()->format('Y-m-d H:i:s'),
                    'guid' => $this->permalinkForId((int) $article->getKey()),
                ]);

            $this->syncArticleDetails((int) $article->getKey(), $username, $validated);

            /** @var ManagedArticle $freshArticle */
            $freshArticle = ManagedArticle::query()->findOrFail($article->getKey());

            return $freshArticle;
        });
    }

    public function trash(ManagedArticle $article): void
    {
        $now = now();

        DB::connection('condo')->table('posts')
            ->where('ID', $article->getKey())
            ->update([
                'post_status' => 'trash',
                'post_modified' => $now->format('Y-m-d H:i:s'),
                'post_modified_gmt' => $now->copy()->utc()->format('Y-m-d H:i:s'),
            ]);
    }

    public function permanentlyDelete(ManagedArticle $article): void
    {
        $thumbnailId = (int) ($article->metaValue('_thumbnail_id') ?? 0);

        if ($thumbnailId > 0) {
            $this->deleteOwnedAttachment($thumbnailId);
        }

        DB::connection('condo')->table('term_relationships')->where('object_id', $article->getKey())->delete();
        DB::connection('condo')->table('postmeta')->where('post_id', $article->getKey())->delete();
        DB::connection('condo')->table('posts')->where('ID', $article->getKey())->delete();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncArticleDetails(int $articleId, string $username, array $validated): void
    {
        $this->upsertMeta($articleId, CondoWordpressBridge::META_USERNAME, $username);
        $this->upsertMeta($articleId, RankMathBridge::TITLE, trim((string) ($validated['meta_title'] ?? '')));
        $this->upsertMeta($articleId, RankMathBridge::DESCRIPTION, trim((string) ($validated['meta_description'] ?? '')));
        $this->upsertMeta($articleId, RankMathBridge::FOCUS_KEYWORD, trim((string) ($validated['focus_keyword'] ?? '')));

        $category = trim((string) ($validated['category'] ?? ''));
        $tags = $this->parseTags((string) ($validated['tags'] ?? ''));

        $this->replaceTaxonomyTerms($articleId, 'category', $category !== '' ? [$category] : []);
        $this->replaceTaxonomyTerms($articleId, 'post_tag', $tags);
        $this->syncFeaturedImage($articleId, trim((string) ($validated['title'] ?? 'Untitled article')), $validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{0:string,1:Carbon}
     */
    private function resolveStatusAndDate(array $validated, ?ManagedArticle $article = null): array
    {
        $requestedStatus = trim((string) ($validated['status'] ?? 'draft'));
        $publishAt = $this->parsePublishAt($validated['publish_at'] ?? null);

        if (! $publishAt instanceof Carbon) {
            $publishAt = $article?->publishedAt() ?? now();
        }

        return match ($requestedStatus) {
            'publish' => [
                $publishAt->gt(now()) ? 'future' : 'publish',
                $publishAt,
            ],
            'schedule' => ['future', $publishAt->gt(now()) ? $publishAt : now()->addMinutes(30)],
            default => ['draft', $publishAt],
        };
    }

    private function parsePublishAt(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function uniqueSlug(string $slugInput, string $fallbackTitle, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($slugInput) !== '' ? $slugInput : $fallbackTitle);
        $base = $base !== '' ? $base : 'article';
        $candidate = $base;
        $suffix = 2;

        while ($this->slugExists($candidate, $ignoreId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = DB::connection('condo')
            ->table('posts')
            ->where('post_type', 'post')
            ->where('post_name', $slug);

        if ($ignoreId !== null) {
            $query->where('ID', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function permalinkForId(int $articleId): string
    {
        return rtrim(CondoWordpressBridge::siteBaseUrl(), '/') . '/?p=' . $articleId;
    }

    private function resolveAuthorId(string $username): int
    {
        $authorId = DB::connection('condo')
            ->table('users')
            ->where('user_login', $username)
            ->value('ID');

        return $authorId ? (int) $authorId : 1;
    }

    /**
     * @return array<int, string>
     */
    private function parseTags(string $tags): array
    {
        return collect(explode(',', $tags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function syncFeaturedImage(int $articleId, string $title, array $validated): void
    {
        $removeFeaturedImage = (bool) ($validated['remove_featured_image'] ?? false);
        $featuredImage = $validated['featured_image'] ?? null;
        $currentAttachmentId = (int) ($this->metaValue($articleId, '_thumbnail_id') ?? 0);

        if ($removeFeaturedImage || $featuredImage instanceof UploadedFile) {
            $this->upsertMeta($articleId, '_thumbnail_id', '');

            if ($currentAttachmentId > 0) {
                $this->deleteOwnedAttachment($currentAttachmentId);
            }
        }

        if (! $featuredImage instanceof UploadedFile || ! $featuredImage->isValid()) {
            return;
        }

        [$payload, $extension] = $this->prepareImagePayload($featuredImage);

        if ($payload === '') {
            return;
        }

        $relativePath = $this->storeAttachmentFile($title, $extension, $payload);
        $attachmentId = $this->createAttachment($articleId, $title, $relativePath, $featuredImage->getMimeType() ?: 'image/' . $extension);

        $this->upsertMeta($articleId, '_thumbnail_id', (string) $attachmentId);
    }

    /**
     * @return array{0:string,1:string}
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

    private function storeAttachmentFile(string $title, string $extension, string $payload): string
    {
        $now = now();
        $directory = $this->absoluteUploadsPath($now->format('Y'), $now->format('m'));

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            abort(500, 'The article upload folder could not be created.');
        }

        $filename = Str::slug($title) ?: 'article-image';
        $filename .= '-' . Str::lower(Str::random(8)) . '.' . ltrim($extension, '.');
        $absolutePath = $directory . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($absolutePath, $payload);

        return $this->relativeUploadsPath($now->format('Y'), $now->format('m'), $filename);
    }

    private function createAttachment(int $articleId, string $title, string $relativePath, string $mimeType): int
    {
        $now = now();
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);

        $attachmentId = DB::connection('condo')->table('posts')->insertGetId([
            'post_author' => 1,
            'post_date' => $now->format('Y-m-d H:i:s'),
            'post_date_gmt' => $now->copy()->utc()->format('Y-m-d H:i:s'),
            'post_content' => '',
            'post_title' => $title !== '' ? $title : $filename,
            'post_excerpt' => '',
            'post_status' => 'inherit',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => Str::slug($filename),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now->format('Y-m-d H:i:s'),
            'post_modified_gmt' => $now->copy()->utc()->format('Y-m-d H:i:s'),
            'post_content_filtered' => '',
            'post_parent' => $articleId,
            'guid' => CondoWordpressBridge::publicUrlForRelativeUploadPath($relativePath),
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
                'meta_key' => '_wp_attachment_image_alt',
                'meta_value' => $title,
            ],
        ]);

        return (int) $attachmentId;
    }

    private function deleteOwnedAttachment(int $attachmentId): void
    {
        $relativePath = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $attachmentId)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        $relativePath = is_string($relativePath) ? trim($relativePath) : '';

        if ($relativePath !== '' && str_starts_with(str_replace('\\', '/', $relativePath), self::UPLOADS_SEGMENT . '/')) {
            $absolutePath = $this->absoluteUploadsPathFromRelative($relativePath);

            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }

            DB::connection('condo')->table('postmeta')->where('post_id', $attachmentId)->delete();
            DB::connection('condo')->table('posts')->where('ID', $attachmentId)->delete();
        }
    }

    /**
     * @param  array<int, string>  $termNames
     */
    private function replaceTaxonomyTerms(int $postId, string $taxonomy, array $termNames): void
    {
        $currentTermTaxonomyIds = DB::connection('condo')
            ->table('term_relationships as relationships')
            ->join('term_taxonomy as taxonomy_table', 'taxonomy_table.term_taxonomy_id', '=', 'relationships.term_taxonomy_id')
            ->where('relationships.object_id', $postId)
            ->where('taxonomy_table.taxonomy', $taxonomy)
            ->pluck('relationships.term_taxonomy_id')
            ->all();

        DB::connection('condo')
            ->table('term_relationships')
            ->where('object_id', $postId)
            ->whereIn('term_taxonomy_id', $currentTermTaxonomyIds)
            ->delete();

        foreach ($currentTermTaxonomyIds as $termTaxonomyId) {
            $this->refreshTermCount((int) $termTaxonomyId);
        }

        foreach (collect($termNames)->map(fn (string $name) => trim($name))->filter()->unique() as $termName) {
            $termTaxonomyId = $this->ensureTermTaxonomyId($taxonomy, $termName);

            DB::connection('condo')->table('term_relationships')->insert([
                'object_id' => $postId,
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
                $query
                    ->where('terms.slug', $slug)
                    ->orWhere('terms.name', $termName);
            })
            ->value('taxonomy_table.term_taxonomy_id');

        if ($existing) {
            return (int) $existing;
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

    private function upsertMeta(int $postId, string $metaKey, string $metaValue): void
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

    private function metaValue(int $postId, string $metaKey): ?string
    {
        $value = DB::connection('condo')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $metaKey)
            ->value('meta_value');

        return is_string($value) ? $value : null;
    }

    private function absoluteUploadsPath(string $year, string $month): string
    {
        return dirname(base_path())
            . DIRECTORY_SEPARATOR . 'wp-content'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . self::UPLOADS_SEGMENT
            . DIRECTORY_SEPARATOR . trim($year)
            . DIRECTORY_SEPARATOR . trim($month);
    }

    private function absoluteUploadsPathFromRelative(string $relativePath): string
    {
        return dirname(base_path())
            . DIRECTORY_SEPARATOR . 'wp-content'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
    }

    private function relativeUploadsPath(string $year, string $month, string $filename): string
    {
        return self::UPLOADS_SEGMENT . '/' . trim($year) . '/' . trim($month) . '/' . ltrim($filename, '/');
    }
}
