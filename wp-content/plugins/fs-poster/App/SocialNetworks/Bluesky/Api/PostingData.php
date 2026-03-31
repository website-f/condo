<?php

namespace FSPoster\App\SocialNetworks\Bluesky\Api;

use FSPoster\App\Providers\Helpers\Date;

class PostingData
{
    public string $collection = 'app.bsky.feed.post';
    public string $type = 'app.bsky.feed.post';
    public string $message;
    public array $uploadMedia;
    public string $createdAt;
    public string $firstComment;
    public string $link;

    public function __construct()
    {
        $this->createdAt = Date::format('Y-m-d\TH:i:s\Z');
    }
}