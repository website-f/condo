<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BridgeSyncStatus extends Model
{
    protected $table = 'cms_bridge_sync_statuses';

    protected $fillable = [
        'agent_username',
        'resource_type',
        'resource_key',
        'sync_target',
        'last_operation',
        'sync_status',
        'last_message',
        'last_error',
        'last_context',
        'last_synced_at',
    ];

    protected $casts = [
        'last_context' => 'array',
        'last_synced_at' => 'datetime',
    ];
}
