<?php

namespace FSPoster\App\Models;

use FSPoster\App\Providers\DB\Model;

/**
 * @property-read int $label_id
 * @property-read int $channel_id
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class ChannelLabelsData extends Model
{
    public static array $writeableColumns = [
        'label_id',
        'channel_id',
        'created_at',
        'updated_at',
    ];

    public static ?string $tableName = 'channel_labels_data';

}