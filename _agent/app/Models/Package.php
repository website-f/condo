<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $table = 'Packages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'creditlimit', 'maxaccount', 'cost'];

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'package', 'id');
    }

    public function getFormattedCostAttribute(): string
    {
        return 'RM ' . number_format((float) $this->cost, 2);
    }
}
