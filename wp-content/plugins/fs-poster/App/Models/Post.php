<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\DB\Model;

/**
 * @property int  $ID
 */
class Post extends Model
{

    protected static bool $isWpCoreTable = true;

}
