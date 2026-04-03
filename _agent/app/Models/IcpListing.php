<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Throwable;

class IcpListing extends Listing
{
    private const TABLE_CANDIDATES = ['MobilePosts', 'mobileposts'];
    private const DETAIL_TABLE_CANDIDATES = ['MobilePostDetails', 'mobilepostdetails'];

    protected $table = 'MobilePosts';

    public static function resolvedConnectionName(): string
    {
        return self::resolvedSchema()['connection'];
    }

    public static function resolvedTableName(): string
    {
        return self::resolvedSchema()['listing_table'];
    }

    public static function resolvedDetailTableName(): string
    {
        return self::resolvedSchema()['detail_table'];
    }

    public function getConnectionName()
    {
        return self::resolvedConnectionName();
    }

    public function getTable()
    {
        return self::resolvedTableName();
    }

    protected static function resolvedSchema(): array
    {
        static $resolvedSchema = null;

        if ($resolvedSchema !== null) {
            return $resolvedSchema;
        }

        foreach (['mysql2', 'mysqlIcp'] as $connection) {
            foreach (self::TABLE_CANDIDATES as $listingTable) {
                foreach (self::DETAIL_TABLE_CANDIDATES as $detailTable) {
                    try {
                        if (
                            Schema::connection($connection)->hasTable($listingTable)
                            && Schema::connection($connection)->hasTable($detailTable)
                        ) {
                            return $resolvedSchema = [
                                'connection' => $connection,
                                'listing_table' => $listingTable,
                                'detail_table' => $detailTable,
                            ];
                        }
                    } catch (Throwable) {
                        continue;
                    }
                }
            }
        }

        return $resolvedSchema = [
            'connection' => 'mysql2',
            'listing_table' => self::TABLE_CANDIDATES[0],
            'detail_table' => self::DETAIL_TABLE_CANDIDATES[0],
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(IcpListingDetail::class, 'postid', 'id');
    }
}
