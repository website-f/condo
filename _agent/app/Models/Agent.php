<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Authenticatable
{
    protected $connection = 'mysql';
    protected $table = 'Users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id', 'username', 'password', 'package', 'createddate',
        'lastlogindate', 'enddate', 'activated', 'adminaccess', 'promotioncode',
    ];

    protected $hidden = ['password'];

    public function detail(): HasOne
    {
        return $this->hasOne(AgentDetail::class, 'username', 'username');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agencyname', 'agencyname');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package', 'id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'username', 'username');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'agent_username', 'username');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'agent_username', 'username');
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'agent_username', 'username');
    }

    public function seoSettings(): HasMany
    {
        return $this->hasMany(SeoSetting::class, 'agent_username', 'username');
    }

    public function getFullNameAttribute(): string
    {
        if ($this->detail) {
            return preg_replace('/\s+/', ' ', trim($this->detail->firstname . ' ' . $this->detail->lastname)) ?: $this->username;
        }
        return $this->username;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->detail?->email;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->detail?->photo_url;
    }
}
