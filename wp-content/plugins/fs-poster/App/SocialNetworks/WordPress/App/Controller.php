<?php

namespace FSPoster\App\SocialNetworks\WordPress\App;

use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\Schedules\SocialNetworkApiException;
use FSPoster\App\SocialNetworks\WordPress\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\WordPress\Api\XmlRpc\Api as XmlRpcApi;
use FSPoster\App\SocialNetworks\WordPress\Api\XmlRpc\AuthData as XmlRpcAuthData;
use FSPoster\App\SocialNetworks\WordPress\Api\RestApi\Api as RestApiApi;
use FSPoster\App\SocialNetworks\WordPress\Api\RestApi\AuthData as RestApiAuthData;

class Controller
{

    /**
     * @throws SocialNetworkApiException
     */
    public static function addChannelViaXmlRpc (RestRequest $request ): array
    {
        $site_url = $request->require( 'site_url', RestRequest::TYPE_STRING, fsp__( 'Please enter the website URL' ) );
        $username = $request->require( 'username', RestRequest::TYPE_STRING, fsp__( 'Please enter the username' ) );
        $password = $request->require( 'password', RestRequest::TYPE_STRING, fsp__( 'Please enter the password' ) );
        $proxy    = $request->param( 'proxy', '', RestRequest::TYPE_STRING );

        if ( !preg_match( '/^http(s|):\/\//i', $site_url ) )
            throw new SocialNetworkApiException( fsp__( 'The URL must start with http(s)' ) );


	    $authData = new XmlRpcAuthData();
	    $authData->siteUrl = $site_url;
		$authData->username = $username;
		$authData->password = $password;

	    $api = new XmlRpcApi();
	    $api->setProxy( $proxy )
	        ->setAuthException( ChannelSessionException::class )
	        ->setAuthData( $authData );

	    $channels = ChannelAdapter::fetchChannels($api, 'XmlRpc');

        return [ 'channels' => $channels ];
    }

    public static function saveSettings ( RestRequest $request ): array
    {
        $postTitle          = $request->param( 'post_title', '', RestRequest::TYPE_STRING );
        $postExcerpt        = $request->param( 'post_excerpt', '', RestRequest::TYPE_STRING );
        $postText           = $request->param( 'post_text', '', RestRequest::TYPE_STRING );
	    $uploadMedia        = $request->param( 'upload_media', false, RestRequest::TYPE_BOOL );
	    $mediaTypeToUpload  = $request->param( 'media_type_to_upload', 'featured_image', RestRequest::TYPE_STRING );
        $postStatus         = $request->param( 'post_status', '', RestRequest::TYPE_STRING );
        $preservePostType   = (int)$request->param( 'preserve_post_type', false, RestRequest::TYPE_BOOL );
        $sendCategories     = (int)$request->param( 'send_categories', false, RestRequest::TYPE_BOOL );
        $sendTags           = (int)$request->param( 'send_tags', false, RestRequest::TYPE_BOOL );

        Settings::set( 'wordpress_post_title', $postTitle );
        Settings::set( 'wordpress_post_excerpt', $postExcerpt );
        Settings::set( 'wordpress_post_content', $postText );
	    Settings::set( 'wordpress_upload_media', (int)$uploadMedia );
	    Settings::set( 'wordpress_media_type_to_upload', $mediaTypeToUpload );
        Settings::set( 'wordpress_post_status', $postStatus );
        Settings::set( 'wordpress_preserve_post_type', $preservePostType );
        Settings::set( 'wordpress_send_categories', $sendCategories );
        Settings::set( 'wordpress_send_tags', $sendTags );

	    do_action( 'fsp_save_settings', $request, Bootstrap::getInstance()->getSlug() );

        return [];
    }

    public static function getSettings ( RestRequest $request ): array
    {
	    return apply_filters('fsp_get_settings', [
		    'post_title'            => Settings::get( 'wordpress_post_title', '{post_title}' ),
		    'post_excerpt'          => Settings::get( 'wordpress_post_excerpt', '{post_excerpt}' ),
		    'post_text'             => Settings::get( 'wordpress_post_content', '{post_content}' ),
		    'upload_media'          => (bool)Settings::get( 'wordpress_upload_media', false ),
		    'media_type_to_upload'  => Settings::get( 'wordpress_media_type_to_upload', 'featured_image' ),
		    'post_status'           => Settings::get( 'wordpress_post_status', 'publish' ),
		    'post_status_options'   => [
			    [
				    'label' => fsp__( 'Publish' ),
				    'value' => 'publish',
			    ],
			    [
				    'label' => fsp__( 'Private' ),
				    'value' => 'private',
			    ],
			    [
				    'label' => fsp__( 'Draft' ),
				    'value' => 'draft',
			    ],
			    [
				    'label' => fsp__( 'Pending' ),
				    'value' => 'pending',
			    ],
		    ],
		    'preserve_post_type'    => (bool)Settings::get( 'wordpress_preserve_post_type', true ),
		    'send_categories'       => (bool)Settings::get( 'wordpress_send_categories', true ),
		    'send_tags'             => (bool)Settings::get( 'wordpress_send_tags', true ),
	    ], Bootstrap::getInstance()->getSlug());
    }

    /**
     * @return array{channels: array}
     * @throws SocialNetworkApiException
     */
    public static function addChannelViaRestApi (RestRequest $request ): array
    {
        $site_url = $request->require( 'site_url', RestRequest::TYPE_STRING, fsp__( 'Please enter the website URL' ) );
        $application_name = $request->require( 'application_name', RestRequest::TYPE_STRING, fsp__( 'Please enter the application name "%s"', [ 'application_name' ] ) );
        $application_password = $request->require( 'application_password', RestRequest::TYPE_STRING, fsp__( 'Please enter the application password "%s"', [ 'application_password' ] ) );
        $proxy             = $request->param( 'proxy', '', RestRequest::TYPE_STRING );

        if ( !preg_match( '/^http(s|):\/\//i', $site_url ) )
            throw new SocialNetworkApiException( fsp__( 'The URL must start with http(s)' ) );

        $authData = new RestApiAuthData();
        $authData->siteUrl = $site_url;
        $authData->applicationName = $application_name;
        $authData->applicationPassword = $application_password;

        $api = new RestApiApi();
        $api->setProxy( $proxy )
            ->setAuthException( ChannelSessionException::class )
            ->setAuthData( $authData );

        $channels = ChannelAdapter::fetchChannels($api, 'RestApi');

        return [ 'channels' => $channels ];
    }

}