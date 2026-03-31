<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\Context\UserContext;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\DB\BlogScope;
use FSPoster\App\Providers\DB\Model;
use FSPoster\App\Providers\DB\QueryBuilder;

/**
 * @property int            $id
 * @property int            $user_id
 * @property string         $type
 * @property string         $title
 * @property int            $blog_id
 * @property string         $message
 * @property string         $action_type
 * @property string         $action_data
 * @property string         $read_at
 * @property string         $created_at
 * @property string         $updated_at
 */
class Notification extends Model
{
    use BlogScope
    {
        BlogScope::booted  as private blogBoot;
    }

    public static array $writeableColumns = [
        'id',
        'user_id',
        'blog_id',
        'type',
        'title',
        'message',
        'action_type',
        'action_data',
        'read_at',
        'created_at',
        'updated_at'
    ];

    public static function booted()
    {
        self::blogBoot();

        $user = Container::get(UserContext::class);
        $userId = $user->id;

        self::addGlobalScope('user_id', static function (QueryBuilder $builder) use ($userId) {
            $builder->where('user_id', $userId);
        });
    }
}
