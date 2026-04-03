<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NewsUpdate extends Model
{
    protected $connection = 'condo';
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $appends = ['rendered_content'];

    protected $fillable = [
        'ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content',
        'post_title', 'post_excerpt', 'post_status', 'comment_status',
        'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged',
        'post_modified', 'post_modified_gmt', 'post_content_filtered',
        'post_parent', 'guid', 'menu_order', 'post_type',
        'post_mime_type', 'comment_count',
    ];

    public function scopeNewsPosts($query)
    {
        return $query->where('post_type', 'post');
    }

    public function scopePublished($query)
    {
        return $query->newsPosts()->where('post_status', 'publish');
    }

    public function scopeManageable($query)
    {
        return $query->newsPosts()->where('post_status', '!=', 'trash');
    }

    public function getFormattedPostDateAttribute(): ?string
    {
        $value = trim((string) ($this->attributes['post_date'] ?? ''));

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M d, Y h:i A');
        } catch (\Throwable) {
            return $value;
        }
    }

    public function getRenderedContentAttribute(): string
    {
        return $this->transformContent($this->attributes['post_content'] ?? '');
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
