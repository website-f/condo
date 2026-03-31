<?php

namespace FSPoster\App\Pages\ChannelLabel\Repositories;

use FSPoster\App\Models\ChannelLabel;
use FSPoster\App\Models\ChannelLabelsData;

class ChannelLabelRepository
{
    public function countByChannelAndLabelIds(int $channelId, array $labelsId): int
    {
        return ChannelLabel::query()->where( 'id', 'in', ChannelLabelsData::query()->where('channel_id', $channelId)->select( 'DISTINCT label_id', true ) )
            ->where('id', 'in', $labelsId)
            ->count();
    }
}
