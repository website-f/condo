<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    protected $table = 'cms_social_accounts';

    protected $fillable = [
        'agent_username', 'platform', 'account_name',
        'access_token', 'refresh_token', 'token_expires_at',
        'page_id', 'is_active',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_username', 'username');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'social_account_id');
    }
}
