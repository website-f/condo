<?php

namespace FSPoster\App\Models;


use FSPoster\App\Providers\DB\Model;

/**
 * @property int $id
 * @property int $workflow_id
 * @property string $driver
 * @property string $data
 */
class WorkflowAction extends Model
{
    public static $relations = [
        'workflow'   =>  [ Workflow::class, 'id', 'workflow_id' ]
    ];

    public static array $writeableColumns = [
        'workflow_id',
        'driver',
        'data',
        'is_active'
    ];
}
