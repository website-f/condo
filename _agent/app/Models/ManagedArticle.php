<?php

namespace App\Models;

use App\Support\CondoWordpressBridge;
use App\Support\RankMathBridge;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManagedArticle extends Model
{
    protected $connection = 'condo';
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'post_type',
        'post_mime_type',
        'comment_count',
    ];

    protected $appends = [
        'status_label',
        'editor_status',
        'formatted_publish_at',
        'public_url',
        'featured_image_url',
        'category_names',
        'tag_names',
        'seo_title',
        'seo_description',
        'focus_keyword',
        'rendered_content',
    ];

    public function scopeArticlePosts(Builder $query): Builder
    {
        return $query->where('post_type', 'post');
    }

    public function scopeManageable(Builder $query): Builder
    {
        return $query->articlePosts()->whereNotIn('post_status', ['trash', 'auto-draft', 'inherit']);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->articlePosts()
            ->where('post_status', 'publish')
            ->where('post_date', '<=', now()->format('Y-m-d H:i:s'));
    }

    public function scopeOwnedByAgent(Builder $query, string $username): Builder
    {
        return $query->where(function (Builder $builder) use ($username) {
            $builder
                ->whereExists(function ($subquery) use ($username) {
                    $subquery
                        ->select(DB::raw(1))
                        ->from('postmeta as owner_meta')
                        ->whereColumn('owner_meta.post_id', 'posts.ID')
                        ->where('owner_meta.meta_key', CondoWordpressBridge::META_USERNAME)
                        ->where('owner_meta.meta_value', $username);
                })
                ->orWhereExists(function ($subquery) use ($username) {
                    $subquery
                        ->select(DB::raw(1))
                        ->from('users as wp_users')
                        ->whereColumn('wp_users.ID', 'posts.post_author')
                        ->where('wp_users.user_login', $username);
                });
        });
    }

    public function scopeTrashedForAgent(Builder $query, string $username): Builder
    {
        return $query
            ->articlePosts()
            ->where('post_status', 'trash')
            ->ownedByAgent($username);
    }

    public function scopeWithCategory(Builder $query, string $category): Builder
    {
        $category = trim($category);

        if ($category === '') {
            return $query;
        }

        $slug = Str::slug($category);

        return $query->whereExists(function ($subquery) use ($category, $slug) {
            $subquery
                ->select(DB::raw(1))
                ->from('term_relationships as relationships')
                ->join('term_taxonomy as taxonomy', 'taxonomy.term_taxonomy_id', '=', 'relationships.term_taxonomy_id')
                ->join('terms as terms', 'terms.term_id', '=', 'taxonomy.term_id')
                ->whereColumn('relationships.object_id', 'posts.ID')
                ->where('taxonomy.taxonomy', 'category')
                ->where(function ($taxonomyQuery) use ($category, $slug) {
                    $taxonomyQuery
                        ->where('terms.name', $category)
                        ->orWhere('terms.slug', $slug);
                });
        });
    }

    public function metaValue(string $metaKey): ?string
    {
        $value = DB::connection($this->getConnectionName())
            ->table('postmeta')
            ->where('post_id', $this->getKey())
            ->where('meta_key', $metaKey)
            ->value('meta_value');

        return is_string($value) ? $value : null;
    }

    /**
     * @return array<int, string>
     */
    public function taxonomyNames(string $taxonomy): array
    {
        return DB::connection($this->getConnectionName())
            ->table('terms as terms')
            ->join('term_taxonomy as taxonomy_table', 'taxonomy_table.term_id', '=', 'terms.term_id')
            ->join('term_relationships as relationships', 'relationships.term_taxonomy_id', '=', 'taxonomy_table.term_taxonomy_id')
            ->where('relationships.object_id', $this->getKey())
            ->where('taxonomy_table.taxonomy', $taxonomy)
            ->orderBy('terms.name')
            ->pluck('terms.name')
            ->map(fn (mixed $name) => trim((string) $name))
            ->filter()
            ->values()
            ->all();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ((string) $this->attributes['post_status']) {
            'publish' => 'Published',
            'future' => 'Scheduled',
            'draft' => 'Draft',
            'pending' => 'Pending',
            'private' => 'Private',
            default => Str::headline((string) $this->attributes['post_status']),
        };
    }

    public function getEditorStatusAttribute(): string
    {
        return (string) $this->attributes['post_status'] === 'future' ? 'schedule' : ((string) $this->attributes['post_status'] ?: 'draft');
    }

    public function getFormattedPublishAtAttribute(): ?string
    {
        $publishedAt = $this->publishedAt();

        return $publishedAt?->format('d M Y, h:i A');
    }

    public function getPublicUrlAttribute(): string
    {
        $guid = trim((string) ($this->attributes['guid'] ?? ''));

        if ($guid !== '' && preg_match('#^https?://#i', $guid) === 1) {
            return $guid;
        }

        return rtrim(CondoWordpressBridge::siteBaseUrl(), '/') . '/?p=' . $this->getKey();
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $attachmentId = (int) ($this->metaValue('_thumbnail_id') ?? 0);

        if ($attachmentId <= 0) {
            return null;
        }

        $attachedFile = DB::connection($this->getConnectionName())
            ->table('postmeta')
            ->where('post_id', $attachmentId)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        if (! is_string($attachedFile) || trim($attachedFile) === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $attachedFile) === 1) {
            return $attachedFile;
        }

        return CondoWordpressBridge::publicUrlForRelativeUploadPath($attachedFile);
    }

    /**
     * @return array<int, string>
     */
    public function getCategoryNamesAttribute(): array
    {
        return $this->taxonomyNames('category');
    }

    /**
     * @return array<int, string>
     */
    public function getTagNamesAttribute(): array
    {
        return $this->taxonomyNames('post_tag');
    }

    public function getSeoTitleAttribute(): ?string
    {
        return $this->metaValue(RankMathBridge::TITLE);
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->metaValue(RankMathBridge::DESCRIPTION);
    }

    public function getFocusKeywordAttribute(): ?string
    {
        return $this->metaValue(RankMathBridge::FOCUS_KEYWORD);
    }

    public function getRenderedContentAttribute(): string
    {
        return $this->transformContent((string) ($this->attributes['post_content'] ?? ''));
    }

    public function publishedAt(): ?Carbon
    {
        $value = trim((string) ($this->attributes['post_date'] ?? ''));

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    protected function transformContent(string $content): string
    {
        $wordpressMediaBaseUrl = rtrim((string) config('services.shared_assets.wordpress_media_base_url'), '/');

        if ($wordpressMediaBaseUrl !== '') {
            $content = preg_replace(
                '#https?://(?:www\.)?condo\.com\.my(?=/wp-content/uploads/)#i',
                $wordpressMediaBaseUrl,
                $content
            ) ?? $content;

            $content = preg_replace(
                '#(?<=[\'"(=])\/wp-content/uploads/#i',
                $wordpressMediaBaseUrl . '/wp-content/uploads/',
                $content
            ) ?? $content;
        }

        return $content;
    }
}
