<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\DB\Model;

/**
 * @property-read int    $id
 * @property-read int    $row_id
 * @property-read string $data_key
 * @property-read string $data_value
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class Data extends Model
{

    protected static ?string $tableName = 'data';

    public static array $writeableColumns = [
        'id',
        'row_id',
        'data_key',
        'data_value',
        'created_at',
        'updated_at',
    ];

    public static $relations = [
        'planners' => [ Planner::class, 'id', 'row_id' ],
    ];
}
