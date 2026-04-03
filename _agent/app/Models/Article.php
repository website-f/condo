<?php

namespace App\Models;

use App\Support\SharedAssetUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $table = 'cms_articles';

    protected $appends = ['featured_image_url'];

    protected $fillable = [
        'agent_username', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'status', 'category', 'tags',
        'meta_title', 'meta_description', 'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_username', 'username');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return SharedAssetUrl::storage($this->attributes['featured_image'] ?? null);
    }
}
