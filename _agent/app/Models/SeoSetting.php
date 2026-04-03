<?php

namespace App\Models;

use App\Support\SharedAssetUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoSetting extends Model
{
    protected $table = 'cms_seo_settings';

    protected $appends = ['og_image_url'];

    protected $fillable = [
        'agent_username', 'page_type', 'page_identifier',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image',
        'canonical_url', 'robots', 'schema_markup',
    ];

    protected $casts = [
        'schema_markup' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_username', 'username');
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return SharedAssetUrl::storage($this->attributes['og_image'] ?? null);
    }
}
