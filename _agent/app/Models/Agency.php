<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    protected $table = 'Agency';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id', 'agencyname', 'package_id', 'linkto',
        'userslimit', 'expireddate', 'createddate', 'plugin',
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(AgentDetail::class, 'agencyname', 'agencyname');
    }
}
