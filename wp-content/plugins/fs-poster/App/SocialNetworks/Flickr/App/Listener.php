<?php

namespace FSPoster\App\SocialNetworks\Flickr\App;

use FSPoster\App\Models\Channel;
use FSPoster\App\Pages\Schedules\CalendarData;
use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\Request;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\DB\Collection;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use FSPoster\App\Providers\Schedules\ScheduleResponseObject;
use FSPoster\App\Providers\Schedules\ScheduleShareException;
use FSPoster\App\SocialNetworks\Flickr\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\Flickr\Adapters\PostingDataAdapter;
use FSPoster\App\SocialNetworks\Flickr\Api\Api;
use FSPoster\App\SocialNetworks\Flickr\Api\AuthData;

class Listener
{

	/**
	 * @throws ChannelSessionException
	 * @throws \Exception
	 */
	public static function sharePost ( ScheduleResponseObject $result, ScheduleObject $scheduleObj ): ScheduleResponseObject
	{
		if ( $scheduleObj->getSocialNetwork() !== Bootstrap::getInstance()->getSlug() )
			return $result;

		$authDataArray = $scheduleObj->getChannelSession()->data_obj->auth_data ?? [];

		$authData = new AuthData();
		$authData->setFromArray( $authDataArray );

		$postingDataAdapter = new PostingDataAdapter( $scheduleObj );
		$postingData = $postingDataAdapter->getPostingData();

		$api = new Api();

		$api->setProxy( $scheduleObj->getChannelSession()->proxy )
		    ->setAuthException( ChannelSessionException::class )
		    ->setPostException( ScheduleShareException::class )
		    ->setAuthData( $authData );

		$response = $api->sendPost( $postingData );

		$snPostResponse = new ScheduleResponseObject();
		$snPostResponse->status         = 'success';
		$snPostResponse->remote_post_id = $response['id'];
		$snPostResponse->data           = [
			'id'  => $response['id'],
			'url' => $response['url'],
		];

		return $snPostResponse;
	}

	public static function getCustomPostData ( array $customPostData, Collection $channel, string $socialNetwork ): array
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
			return $customPostData;

		$channelSettings = $channel->custom_settings_obj->custom_post_data;

		if ( ! empty( $channelSettings['use_custom_photo_title'] ) )
			$customPostData['post_title'] = $channelSettings['photo_title'];
		else
			$customPostData['post_title'] = Settings::get( 'flickr_photo_title', '{post_title}' );

		$customPostData['send_tags']     = (bool)Settings::get( 'flickr_send_tags', true );
		$customPostData['upload_media_type'] = 'featured_image';
		$customPostData['privacy']       = Settings::get( 'flickr_privacy', 'public' );
		$customPostData['album_id']      = $channelSettings['default_album_id'] ?? '';
		$customPostData['album_name']    = $channelSettings['default_album_name'] ?? '';

		if ( (bool)Settings::get( 'flickr_share_to_first_comment', false ) )
			$customPostData['first_comment'] = Settings::get( 'flickr_first_comment', '' );
		else
			$customPostData['first_comment'] = '';

		if ( ! empty( $channelSettings['use_custom_post_content'] ) )
			$customPostData['post_content'] = $channelSettings['post_content'];
		else
			$customPostData['post_content'] = Settings::get( 'flickr_photo_description', '{post_content limit="497"}' );

		return $customPostData;
	}

	public static function disableSocialChannel ( string $socialNetwork, Collection $channel, Collection $channelSession ): void
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
			return;

		Channel::where( 'channel_session_id', $channelSession->id )->update( [ 'status' => 0 ] );
	}

	/**
	 * @throws \Exception
	 */
	public static function addApp ( array $data, string $socialNetwork, RestRequest $request ): array
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
			return $data;

		$consumerKey    = $request->require( 'consumer_key', RestRequest::TYPE_STRING, fsp__( 'Consumer Key is empty' ) );
		$consumerSecret = $request->require( 'consumer_secret', RestRequest::TYPE_STRING, fsp__( 'Consumer Secret is empty' ) );

		return [
			'consumer_key'    => $consumerKey,
			'consumer_secret' => $consumerSecret,
		];
	}

	public static function getCalendarData ( CalendarData $calendarData, ScheduleObject $scheduleObj )
	{
		if ( $scheduleObj->getSocialNetwork() !== Bootstrap::getInstance()->getSlug() )
			return $calendarData;

		$postingData = new PostingDataAdapter( $scheduleObj );

		$calendarData->content   = $postingData->getPostingDataTitle();
		$calendarData->mediaList = $postingData->getPostingDataUploadMedia();

		return $calendarData;
	}

	public static function getAuthURL ( $url, $socialNetwork, $app, $proxy )
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() || $app->social_network !== Bootstrap::getInstance()->getSlug() )
			return $url;

		$callbackUrl = Bootstrap::getInstance()->getCallbackUrl();

		$result = Api::getAuthURL(
			$app->data_obj->consumer_key,
			$app->data_obj->consumer_secret,
			$callbackUrl,
			$proxy
		);

		// Store the request token secret temporarily in a transient for the callback
		set_transient( 'fsp_flickr_oauth_token_secret_' . $result['oauth_token'], $result['oauth_token_secret'], 600 );

		return $result['url'];
	}

	public static function getAuthChannels ( array $data, string $socialNetwork, Collection $app, ?string $proxy ): array
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() || $app->social_network !== Bootstrap::getInstance()->getSlug() )
			return $data;

		$oauthToken    = Request::get( 'oauth_token', '', 'str' );
		$oauthVerifier = Request::get( 'oauth_verifier', '', 'str' );

		if ( empty( $oauthToken ) || empty( $oauthVerifier ) )
			throw new ChannelSessionException( fsp__( 'OAuth verification failed' ) );

		// Retrieve the stored request token secret
		$oauthTokenSecret = get_transient( 'fsp_flickr_oauth_token_secret_' . $oauthToken );
		delete_transient( 'fsp_flickr_oauth_token_secret_' . $oauthToken );

		if ( empty( $oauthTokenSecret ) )
			throw new ChannelSessionException( fsp__( 'OAuth session expired. Please try again.' ) );

		$authData = new AuthData();
		$authData->consumerKey    = $app->data_obj->consumer_key;
		$authData->consumerSecret = $app->data_obj->consumer_secret;

		$api = new Api();
		$api->setProxy( $proxy )
		    ->setAuthException( ChannelSessionException::class )
		    ->setAuthData( $authData );

		$api->fetchAccessToken( $oauthToken, $oauthTokenSecret, $oauthVerifier );

		$channels = ChannelAdapter::fetchChannels( $api );

		return [
			'channels' => $channels,
		];
	}

	public static function getStandardAppChannels ( array $data, string $socialNetwork, Collection $app, ?string $proxy ): array
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
			return $data;

		$oauthToken       = Request::get( 'oauth_token', '', 'string' );
		$oauthTokenSecret = Request::get( 'oauth_token_secret', '', 'string' );

		if ( empty( $oauthToken ) || empty( $oauthTokenSecret ) )
			return $data;

		$consumerKey    = $app->data_obj->api_key ?? '';
		$consumerSecret = $app->data_obj->api_key_secret ?? '';

		if ( empty( $consumerKey ) || empty( $consumerSecret ) )
			return [ 'error_msg' => fsp__( 'App credentials are missing' ) ];

		$authData = new AuthData();
		$authData->consumerKey      = $consumerKey;
		$authData->consumerSecret   = $consumerSecret;
		$authData->oauthToken       = urldecode( $oauthToken );
		$authData->oauthTokenSecret = urldecode( $oauthTokenSecret );

		$api = new Api();
		$api->setProxy( $proxy )
		    ->setAuthException( ChannelSessionException::class )
		    ->setAuthData( $authData );

		$channels = ChannelAdapter::fetchChannels( $api );

		return [
			'channels' => $channels,
		];
	}

	public static function refreshChannel ( array $updatedChannel, string $socialNetwork, Collection $channel, Collection $channelSession )
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
			return $updatedChannel;

		$authDataArray = $channelSession->data_obj->auth_data ?? [];

		$authData = new AuthData();
		$authData->setFromArray( $authDataArray );

		$api = new Api();
		$api->setProxy( $channelSession->proxy )
		    ->setAuthException( ChannelSessionException::class )
		    ->setAuthData( $authData );

		$refreshedChannels = ChannelAdapter::fetchChannels( $api );

		foreach ( $refreshedChannels as $refreshedChannel )
		{
			if ( $refreshedChannel['remote_id'] == $channel->remote_id )
			{
				return $refreshedChannel;
			}
		}

		return $updatedChannel;
	}

	public static function getPostLink ( string $channelLink, ScheduleObject $scheduleObj ): string
	{
		if ( $scheduleObj->getSocialNetwork() !== Bootstrap::getInstance()->getSlug() )
			return $channelLink;

		return $scheduleObj->getSchedule()->data_obj['url'] ?? '';
	}

	public static function getChannelLink ( string $channelLink, string $socialNetwork, Collection $channel ): string
	{
		if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
			return $channelLink;

		return $channel->data_obj->url ?? ( 'https://www.flickr.com/photos/' . $channel->remote_id . '/' );
	}
}
