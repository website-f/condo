<?php

namespace FSPoster\App\SocialNetworks\Flickr\Adapters;

use FSPoster\App\Models\Channel;
use FSPoster\App\Providers\Channels\ChannelService;
use FSPoster\App\SocialNetworks\Flickr\Api\Api;
use FSPoster\App\SocialNetworks\Flickr\App\Bootstrap;

class ChannelAdapter
{

	/**
	 * Fetch Flickr user info and create channel session + channel.
	 *
	 * @param Api $api
	 *
	 * @return array
	 */
	public static function fetchChannels ( Api $api ): array
	{
		$data = $api->getMyInfo();

		if ( empty( $api->authData->nsid ) )
			$api->authData->nsid = $data['id'];

		$channelSessionId = ChannelService::addChannelSession( [
			'name'           => $data['username'] ?: $data['realname'],
			'social_network' => Bootstrap::getInstance()->getSlug(),
			'remote_id'      => $data['id'],
			'proxy'          => $api->proxy,
			'method'         => 'app',
			'data'           => [
				'auth_data' => (array)$api->authData,
			],
		] );

		$existingChannels = Channel::where( 'channel_session_id', $channelSessionId )
		                           ->select( [ 'id', 'remote_id' ], true )
		                           ->fetchAll();

		$existingChannelsIdToRemoteIdMap = [];

		foreach ( $existingChannels as $existingChannel )
		{
			$existingChannelsIdToRemoteIdMap[ $existingChannel->remote_id ] = $existingChannel->id;
		}

		// Flickr has one channel per user (their photostream)
		$channelsList = [
			[
				'id'                 => $existingChannelsIdToRemoteIdMap[ $data['id'] ] ?? null,
				'social_network'     => Bootstrap::getInstance()->getSlug(),
				'name'               => $data['username'] ?: $data['realname'],
				'channel_type'       => 'account',
				'channel_session_id' => $channelSessionId,
				'remote_id'          => $data['id'],
				'picture'            => $data['picture'],
				'data'               => [
					'url' => $data['url'],
				],
			],
		];

		return $channelsList;
	}
}
