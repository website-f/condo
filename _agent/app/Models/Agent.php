<?php

namespace App\Models;

use Carbon\Carbon;
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
        'credit', 'resetcredit', 'upgradecredit', 'creditenddate',
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

    public function getHasCondoPackageAccessAttribute(): bool
    {
        $this->loadMissing('subscription');

        return (bool) $this->subscription?->is_condo_package;
    }

    public function getCurrentDailyCreditAttribute(): int
    {
        return max(0, (int) ($this->attributes['credit'] ?? 0));
    }

    public function getDailyCreditResetLabelAttribute(): ?string
    {
        $value = trim((string) ($this->attributes['creditenddate'] ?? ''));

        return $value !== '' ? $value : null;
    }

    public function getFormattedCreatedDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['createddate'] ?? null);
    }

    public function getFormattedEndDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['enddate'] ?? null);
    }

    public function getFormattedLastLoginDateAttribute(): ?string
    {
        return $this->formatLegacyDateValue($this->attributes['lastlogindate'] ?? null);
    }

    protected function formatLegacyDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            if (ctype_digit($value) && strlen($value) === 14) {
                return Carbon::createFromFormat('YmdHis', $value, config('app.timezone'))->format('M d, Y h:i A');
            }

            if (ctype_digit($value) && strlen($value) === 10) {
                return Carbon::createFromTimestamp((int) $value, config('app.timezone'))->format('M d, Y h:i A');
            }

            if (ctype_digit($value) && strlen($value) === 8) {
                return Carbon::createFromFormat('Ymd', $value, config('app.timezone'))->format('M d, Y');
            }

            return Carbon::parse($value, config('app.timezone'))->format('M d, Y h:i A');
        } catch (\Throwable) {
            return $value;
        }
    }
}
