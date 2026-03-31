<?php

namespace FSPoster\App\SocialNetworks\Flickr\Api;

class PostingData
{
	public string $title = '';
	public string $description = '';
	public string $tags = '';
	public array $uploadMedia = [];
	public string $albumId = '';
	public int $isPublic = 1;
	public int $isFriend = 0;
	public int $isFamily = 0;
	public string $firstComment = '';
}
