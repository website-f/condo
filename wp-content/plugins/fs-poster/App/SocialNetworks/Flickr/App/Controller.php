<?php

namespace FSPoster\App\SocialNetworks\Flickr\App;

use FSPoster\App\Providers\Channels\ChannelService;
use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\SocialNetworks\Flickr\Api\Api;
use FSPoster\App\SocialNetworks\Flickr\Api\AuthData;

class Controller
{

	public static function saveSettings ( RestRequest $request ): array
	{
		$photoTitle           = $request->param( 'photo_title', '', RestRequest::TYPE_STRING );
		$photoDesc            = $request->param( 'photo_description', '', RestRequest::TYPE_STRING );
		$sendTags             = (int)$request->param( 'send_tags', false, RestRequest::TYPE_BOOL );
		$privacy              = $request->param( 'privacy', 'public', RestRequest::TYPE_STRING, [ 'public', 'friends', 'family', 'friends_and_family', 'private' ] );
		$shareToFirstComment  = (int)$request->param( 'share_to_first_comment', false, RestRequest::TYPE_BOOL );
		$firstComment         = $request->param( 'first_comment', '', RestRequest::TYPE_STRING );

		Settings::set( 'flickr_photo_title', $photoTitle );
		Settings::set( 'flickr_photo_description', $photoDesc );
		Settings::set( 'flickr_send_tags', $sendTags );
		Settings::set( 'flickr_privacy', $privacy );
		Settings::set( 'flickr_share_to_first_comment', $shareToFirstComment );
		Settings::set( 'flickr_first_comment', $firstComment );

		do_action( 'fsp_save_settings', $request, Bootstrap::getInstance()->getSlug() );

		return [];
	}

	public static function getAlbums ( RestRequest $request ): array
	{
		$channelId = $request->require( 'channel_id', RestRequest::TYPE_INTEGER, fsp__( 'Channel ID is required' ) );

		$channelSession = ChannelService::getChannelSessionByChannelId( $channelId, Bootstrap::getInstance()->getSlug() );

		if ( ! $channelSession )
			return [ 'albums' => [] ];

		$authDataArray = $channelSession->data_obj->auth_data ?? [];

		$authData = new AuthData();
		$authData->setFromArray( $authDataArray );

		$api = new Api();
		$api->setProxy( $channelSession->proxy )
		    ->setAuthException( ChannelSessionException::class )
		    ->setAuthData( $authData );

		return [ 'albums' => $api->getAlbums() ];
	}

	public static function getSettings ( RestRequest $request ): array
	{
		return apply_filters( 'fsp_get_settings', [
			'photo_title'             => Settings::get( 'flickr_photo_title', '{post_title}' ),
			'photo_description'       => Settings::get( 'flickr_photo_description', '{post_content limit="497"}' ),
			'send_tags'               => (bool)Settings::get( 'flickr_send_tags', true ),
			'privacy'                 => Settings::get( 'flickr_privacy', 'public' ),
			'privacy_options'         => [
				[ 'label' => fsp__( 'Public' ), 'value' => 'public' ],
				[ 'label' => fsp__( 'Friends' ), 'value' => 'friends' ],
				[ 'label' => fsp__( 'Family' ), 'value' => 'family' ],
				[ 'label' => fsp__( 'Friends & Family' ), 'value' => 'friends_and_family' ],
				[ 'label' => fsp__( 'Private' ), 'value' => 'private' ],
			],
			'share_to_first_comment'  => (bool)Settings::get( 'flickr_share_to_first_comment', false ),
			'first_comment'           => Settings::get( 'flickr_first_comment', '' ),
		], Bootstrap::getInstance()->getSlug() );
	}

}
