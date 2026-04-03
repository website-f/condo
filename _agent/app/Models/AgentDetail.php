<?php

namespace App\Models;

use App\Support\LegacyText;
use App\Support\SharedAssetUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentDetail extends Model
{
    protected $table = 'UserDetails';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $appends = ['photo_url'];

    protected $fillable = [
        'id', 'username', 'email', 'firstname', 'lastname',
        'phone', 'icnumber', 'photo', 'agencyname',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'username', 'username');
    }

    public function getFirstnameAttribute($value): ?string
    {
        return LegacyText::decode($value);
    }

    public function setFirstnameAttribute($value): void
    {
        $this->attributes['firstname'] = LegacyText::encode($value);
    }

    public function getLastnameAttribute($value): ?string
    {
        return LegacyText::decode($value);
    }

    public function setLastnameAttribute($value): void
    {
        $this->attributes['lastname'] = LegacyText::encode($value);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return SharedAssetUrl::profile($this->attributes['photo'] ?? null);
    }
}
