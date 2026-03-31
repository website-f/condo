<?php

namespace FSPoster\App\Pages\ChannelLabel\Services;

use FSPoster\App\Pages\ChannelLabel\Repositories\ChannelLabelRepository;

class ChannelLabelService
{
    private ChannelLabelRepository $repository;
    
    public function __construct(ChannelLabelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function countByChannelAndLabelIds(int $channelId, array $labelIds): int
    {
        return $this->repository->countByChannelAndLabelIds($channelId, $labelIds);
    }
}
