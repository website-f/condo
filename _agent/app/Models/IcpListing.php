<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Throwable;

class IcpListing extends Listing
{
    protected $table = 'mobileposts';

    public static function resolvedConnectionName(): string
    {
        static $resolvedConnection = null;

        if ($resolvedConnection !== null) {
            return $resolvedConnection;
        }

        foreach (['mysql2', 'mysqlIcp'] as $connection) {
            try {
                if (
                    Schema::connection($connection)->hasTable('mobileposts')
                    && Schema::connection($connection)->hasTable('mobilepostdetails')
                ) {
                    return $resolvedConnection = $connection;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return $resolvedConnection = 'mysql2';
    }

    public function getConnectionName()
    {
        return self::resolvedConnectionName();
    }

    public function details(): HasMany
    {
        return $this->hasMany(IcpListingDetail::class, 'postid', 'id');
    }
}
