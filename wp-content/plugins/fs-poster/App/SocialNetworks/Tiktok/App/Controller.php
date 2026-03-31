<?php

namespace FSPoster\App\SocialNetworks\Tiktok\App;


use FSPoster\App\Providers\Channels\ChannelService;
use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\SocialNetworks\Tiktok\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\Tiktok\Api\Api;
use FSPoster\App\SocialNetworks\Tiktok\Api\AuthData;

class Controller
{

    public static function saveSettings ( RestRequest $request ): array
    {
        $photoTitle = $request->param( 'photo_title', '', RestRequest::TYPE_STRING );
        $postText = $request->param( 'post_text', '', RestRequest::TYPE_STRING );
        $privacyLevel = $request->param( 'privacy_level', '', RestRequest::TYPE_STRING );
        $disableDuet = $request->param( 'disable_duet', false, RestRequest::TYPE_BOOL );
        $disableComment = $request->param( 'disable_comment', false, RestRequest::TYPE_BOOL );
        $disableStitch = $request->param( 'disable_stitch', false, RestRequest::TYPE_BOOL );
        $autoAddMusicToPhoto = $request->param( 'auto_add_music_to_photo', true, RestRequest::TYPE_BOOL );

	    $allowedPrivacyLevels = [
		    'PUBLIC_TO_EVERYONE',
		    'MUTUAL_FOLLOW_FRIENDS',
		    'FOLLOWER_OF_CREATOR',
		    'SELF_ONLY'
	    ];

	    $privacyLevel = in_array( $privacyLevel, $allowedPrivacyLevels ) ? $privacyLevel : $allowedPrivacyLevels[0];

        Settings::set( 'tiktok_photo_title', $photoTitle );
        Settings::set( 'tiktok_post_content', $postText );
        Settings::set( 'tiktok_privacy_level', $privacyLevel );
        Settings::set( 'tiktok_disable_duet', (int)$disableDuet );
        Settings::set( 'tiktok_disable_comment', (int)$disableComment );
        Settings::set( 'tiktok_disable_stitch', (int)$disableStitch );
        Settings::set( 'tiktok_auto_add_music_to_photo', (int)$autoAddMusicToPhoto );

	    do_action( 'fsp_save_settings', $request, Bootstrap::getInstance()->getSlug() );

        return [];
    }

    public static function getSettings ( RestRequest $request ): array
    {
	    return apply_filters('fsp_get_settings', [
	      'photo_title'               => Settings::get( 'tiktok_photo_title', '{photo_title}' ),
		    'post_text'                 => Settings::get( 'tiktok_post_content', '{post_title}' ),
		    'privacy_level'             => Settings::get( 'tiktok_privacy_level', 'PUBLIC_TO_EVERYONE' ),
		    'disable_duet'              => (bool)Settings::get( 'tiktok_disable_duet', false ),
		    'disable_comment'           => (bool)Settings::get( 'tiktok_disable_comment', false ),
		    'disable_stitch'            => (bool)Settings::get( 'tiktok_disable_stitch', false ),
		    'auto_add_music_to_photo'   => (bool)Settings::get( 'tiktok_auto_add_music_to_photo', true ),
	    ], Bootstrap::getInstance()->getSlug());
    }

    public static function getCreatorInfo(RestRequest $request): array
    {
        $channelId = $request->param( 'channel_id', '', RestRequest::TYPE_INTEGER );

        $channelSession = ChannelService::getChannelSessionByChannelId($channelId, Bootstrap::getInstance()->getSlug());

        if (! $channelSession) {
            return [];
        }

        $authDataArray = $channelSession->data_obj->auth_data;

        $authData = new AuthData();
        $authData->setFromArray($authDataArray);

        $api = new Api();
        $api->setAuthException( ChannelSessionException::class )
            ->setAuthData( $authData );

        ChannelAdapter::refreshAndUpdateChannelSessionIfNeeded($channelSession->id, $api);

        return $api->creatorInfoQuery();
    }

}