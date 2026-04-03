<?php

namespace App\Models;

use App\Support\SharedAssetUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    protected $table = 'cms_social_posts';

    protected $appends = ['media_asset_url'];

    protected $fillable = [
        'agent_username', 'social_account_id', 'content',
        'media_url', 'platform', 'status', 'scheduled_at',
        'published_at', 'external_post_id', 'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_username', 'username');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'scheduled')->where('scheduled_at', '>', now());
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getMediaAssetUrlAttribute(): ?string
    {
        return SharedAssetUrl::storage($this->attributes['media_url'] ?? null);
    }
}
