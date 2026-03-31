<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\DB\BlogScope;
use FSPoster\App\Providers\DB\Model;

/**
 * @property int $id
 * @property int $workflow_id
 * @property string $when
 * @property string $driver
 * @property string $created_at
 * @property string $updated_at
 * @property string $request_data
 * @property string $response_data
 * @property string $status
 * @property string|null $error_msg
 * @property int $blog_id
 */

class WorkflowLog extends Model
{
    use BlogScope;

    public static array $writeableColumns = [
        'workflow_id',
        'when',
        'driver',
        'request_data',
        'response_data',
        'blog_id',
        'status',
        'error_msg'
    ];
}
