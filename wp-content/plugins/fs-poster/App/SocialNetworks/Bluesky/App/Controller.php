<?php

namespace FSPoster\App\SocialNetworks\Bluesky\App;

use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\Schedules\SocialNetworkApiException;
use FSPoster\App\SocialNetworks\Bluesky\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\Bluesky\Api\Api;
use FSPoster\App\SocialNetworks\Bluesky\Api\AuthData;
use FSPoster\App\SocialNetworks\Telegram\App\Bootstrap;

class Controller
{
    public static function saveSettings(RestRequest $request)
    {
        $postText            = $request->param( 'post_text', '', RestRequest::TYPE_STRING );
        $shareToFirstComment = (int)$request->param( 'share_to_first_comment', false, RestRequest::TYPE_BOOL );
        $firstCommentText    = $request->param( 'first_comment_text', '', RestRequest::TYPE_STRING );
        $attachLink         = $request->param( 'attach_link', true, RestRequest::TYPE_BOOL );
        $uploadMedia        = $request->param( 'upload_media', false, RestRequest::TYPE_BOOL );
        $mediaTypeToUpload  = $request->param( 'media_type_to_upload', 'featured_image', RestRequest::TYPE_STRING );

        if ( $shareToFirstComment && empty( $firstCommentText ) )
            throw new \Exception( fsp__( 'First comment can\'t be empty if enabled' ) );

        if( ! $shareToFirstComment )
            $firstCommentText = '';

        Settings::set( 'bluesky_post_content', $postText );
        Settings::set( 'bluesky_share_to_first_comment', $shareToFirstComment );
        Settings::set( 'bluesky_first_comment_text', $firstCommentText );
        Settings::set( 'bluesky_attach_link', (int)$attachLink );
        Settings::set( 'bluesky_upload_media', (int)$uploadMedia );
        Settings::set( 'bluesky_media_type_to_upload', $mediaTypeToUpload );

        do_action( 'fsp_save_settings', $request, Bootstrap::getInstance()->getSlug() );

        return [];
    }

    public static function getSettings(RestRequest $request)
    {
        return apply_filters('fsp_get_settings', [
            'post_text'                 => Settings::get( 'bluesky_post_content', '{post_title}' ),
            'share_to_first_comment'    => (bool)Settings::get( 'bluesky_share_to_first_comment', false ),
            'first_comment_text'        => Settings::get( 'bluesky_first_comment_text', '' ),
            'attach_link'               => (bool)Settings::get( 'bluesky_attach_link', true ),
            'upload_media'              => (bool)Settings::get( 'bluesky_upload_media', false ),
            'media_type_to_upload'      => Settings::get( 'bluesky_media_type_to_upload', 'featured_image' )
        ], Bootstrap::getInstance()->getSlug());
    }

    /**
     * @throws SocialNetworkApiException
     * @throws ChannelSessionException
     */
    public static function addChannelViaAppPassword(RestRequest $request): array
    {
        $identifier = $request->require( 'identifier', RestRequest::TYPE_STRING, fsp__( 'Please enter the identifier' ) );
        $appPassword = $request->require( 'app_password', RestRequest::TYPE_STRING, fsp__( 'Please enter the app password' ) );
        $proxy    = $request->param( 'proxy', '', RestRequest::TYPE_STRING );

        /**
         * Bele yazmagda sebeb, bluesky-dan username copy edende nullbyte dushur ve 401 qayidir neticede
         */
        $identifier = preg_replace('/[^a-z0-9.\-_]/i', '', strtolower($identifier));

        $authData = new AuthData();
        $authData->setFromArray( [ 'identifier' => $identifier, 'appPassword' => $appPassword ] );

        $api = new Api();

        $api->setProxy( $proxy )
            ->setAuthException( ChannelSessionException::class )
            ->setAuthData( $authData )
            ->createSession();

        $channels = ChannelAdapter::fetchChannels( $api );

        return [
            'channels' => $channels
        ];
    }
}