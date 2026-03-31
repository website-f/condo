<?php

namespace FSPoster\App\SocialNetworks\TruthSocial\App;

use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\SocialNetworks\TruthSocial\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\TruthSocial\Api\Api;
use FSPoster\App\SocialNetworks\TruthSocial\Api\AuthData;

class Controller
{
    public static function saveSettings ( RestRequest $request ): array
    {
        $postText           = $request->param( 'post_text', '', RestRequest::TYPE_STRING );
	    $attachLink         = $request->param( 'attach_link', true, RestRequest::TYPE_BOOL );
	    $uploadMedia        = $request->param( 'upload_media', false, RestRequest::TYPE_BOOL );
	    $mediaTypeToUpload  = $request->param( 'media_type_to_upload', 'featured_image', RestRequest::TYPE_STRING );

        Settings::set( 'truthsocial_post_content', $postText );
	    Settings::set( 'truthsocial_attach_link', (int)$attachLink );
	    Settings::set( 'truthsocial_upload_media', (int)$uploadMedia );
	    Settings::set( 'truthsocial_media_type_to_upload', $mediaTypeToUpload );

	    do_action( 'fsp_save_settings', $request, Bootstrap::getInstance()->getSlug() );

        return [];
    }

    public static function getSettings ( RestRequest $request ): array
    {
	    return apply_filters('fsp_get_settings', [
		    'post_text'             => Settings::get( 'truthsocial_post_content', '{post_title}' ),
		    'attach_link'           => (bool)Settings::get( 'truthsocial_attach_link', true ),
		    'upload_media'          => (bool)Settings::get( 'truthsocial_upload_media', false ),
		    'media_type_to_upload'  => Settings::get( 'truthsocial_media_type_to_upload', 'featured_image' )
	    ], Bootstrap::getInstance()->getSlug());
    }

    public static function addChannelViaAccessToken ( RestRequest $request ): array
    {
        $accessToken = $request->require( 'access_token', RestRequest::TYPE_STRING, fsp__( 'Access token is empty' ) );
        $account     = $request->require( 'account', RestRequest::TYPE_ARRAY, fsp__( 'Account data is empty' ) );
        $proxy       = $request->param( 'proxy', '', RestRequest::TYPE_STRING );

        $authData = new AuthData();
        $authData->server = Api::SERVER;
        $authData->accessToken = $accessToken;

        $api = new Api();
        $api->setProxy( $proxy )
            ->setAuthException( ChannelSessionException::class )
            ->setAuthData( $authData );

        $channels = ChannelAdapter::fetchChannelsFromAccountData( $api, $account );

        return [
            'channels' => $channels
        ];
    }
}
