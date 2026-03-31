<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\DB\Model;

/**
 * @property-read int    $channel_id
 * @property-read string $user_role
 * @property-read string $permission = 'full_access' | 'can_share'
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class ChannelPermission extends Model
{
    public static array $writeableColumns = [
        'channel_id',
        'user_role',
        'permission',
        'created_at',
        'updated_at',
    ];
}
