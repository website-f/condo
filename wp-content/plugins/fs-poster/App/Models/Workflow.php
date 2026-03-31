<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\DB\BlogScope;
use FSPoster\App\Providers\DB\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $when
 * @property string $data
 * @property int $is_active
 * @method WorkflowAction workflow_actions()
 * @method WorkflowLog workflow_logs()
 */
class Workflow extends Model
{
    use BlogScope;

    public static $relations = [
        'workflow_actions'  => [ WorkflowAction::class, 'workflow_id', 'id' ],
        'workflow_logs'     => [ WorkflowLog::class, 'workflow_id', 'id' ]
    ];

    public static array $writeableColumns = [
        'name',
        'when',
        'blog_id',
        'data',
        'is_active'
    ];
}
