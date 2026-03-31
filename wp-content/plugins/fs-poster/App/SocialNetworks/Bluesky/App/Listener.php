<?php

namespace FSPoster\App\SocialNetworks\Bluesky\App;

use FSPoster\App\Models\Channel;
use FSPoster\App\Pages\Schedules\CalendarData;
use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\DB\Collection;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use FSPoster\App\Providers\Schedules\ScheduleResponseObject;
use FSPoster\App\Providers\Schedules\ScheduleShareException;
use FSPoster\App\Providers\Schedules\SocialNetworkApiException;
use FSPoster\App\SocialNetworks\Bluesky\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\Bluesky\Adapters\PostingDataAdapter;
use FSPoster\App\SocialNetworks\Bluesky\Api\Api;
use FSPoster\App\SocialNetworks\Bluesky\Api\AuthData;

class Listener
{
    /**
     * @throws \Exception
     */
    public static function sharePost(ScheduleResponseObject $result, ScheduleObject $scheduleObj): ScheduleResponseObject
    {
        if ( $scheduleObj->getSocialNetwork() !== Bootstrap::getInstance()->getSlug() )
            return $result;

        $postingDataAdapter = new PostingDataAdapter( $scheduleObj );
        $postingData = $postingDataAdapter->getPostingData();

        $authData = new AuthData();
        $authData->setFromArray( $scheduleObj->getChannelSession()->data_obj->auth_data ?? [] );

        $api = new Api();

        $api->setProxy( $scheduleObj->getChannelSession()->proxy )
            ->setAuthException( ChannelSessionException::class )
            ->setPostException( ScheduleShareException::class )
            ->setAuthData( $authData );

        ChannelAdapter::refreshAndUpdateChannelSessionIfNeeded($scheduleObj->getChannelSession(), $api);

        $response = $api->sendPost( $postingData );

        $result = new ScheduleResponseObject();
        $result->status = 'success';
        $result->remote_post_id = $response['cid'];
        $result->data = [
            'uri' => $response['uri'],
            'handle' => $authData->identifier
        ];

        return $result;
    }

    public static function getCustomPostData(array $customPostData, Collection $channel, string $socialNetwork)
    {
        if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
            return $customPostData;

        $channelSettings = $channel->custom_settings_obj->custom_post_data;

        $customPostData["attach_link"] = (bool)Settings::get( 'bluesky_attach_link', true );
        $customPostData["upload_media"] = (bool)Settings::get( 'bluesky_upload_media', false );
        $customPostData["upload_media_type"] = Settings::get( 'bluesky_media_type_to_upload', 'featured_image' );

        if( Settings::get( 'bluesky_share_to_first_comment', false ) )
            $customPostData["first_comment"] = Settings::get( 'bluesky_first_comment_text', '' );

        if( ! empty( $channelSettings[ 'use_custom_post_content' ] ) )
            $customPostData['post_content'] = $channelSettings[ 'post_content' ];
        else
            $customPostData['post_content'] = Settings::get( 'bluesky_post_content', '{post_title}' );

        return $customPostData;
    }

    public static function getInsights ( array $insights, string $social_network, Collection $schedule ): array
    {
        if ( $social_network !== Bootstrap::getInstance()->getSlug() )
            return $insights;

        $channel = Channel::where( 'id', $schedule->channel_id )->fetch();
        $channelSession = $channel->channel_session->fetch();

        $api = new Api();
        $api->setProxy( $channelSession->proxy )
            ->setAuthException( ChannelSessionException::class );

        $stats = $api->getStats( json_decode($schedule->data, true)['uri'] );

        return array_merge( $insights, $stats );
    }

    public static function getCalendarData( CalendarData $calendarData, ScheduleObject $scheduleObj )
    {
        if ( $scheduleObj->getSocialNetwork() !== Bootstrap::getInstance()->getSlug() )
            return $calendarData;

        $postingData = new PostingDataAdapter( $scheduleObj );

        $calendarData->content   = $postingData->getPostingDataMessage();
        $calendarData->mediaList = $postingData->getPostingDataUploadMedia();

        return $calendarData;
    }

    public static function getPostLink(string $postLink, ScheduleObject $scheduleObj ): string
    {
        if ( $scheduleObj->getSocialNetwork() !== Bootstrap::getInstance()->getSlug() )
            return $postLink;

        $schedule = $scheduleObj->getSchedule();
        $uri = $schedule->data_obj->uri ?? '-';
        $uriParse =  explode('/', $uri);
        $handle = $schedule->data_obj->handle ?? '-';

        return 'https://bsky.app/profile/' . $handle . '/post/' . ($uriParse[4] ?? '-');
    }

    public static function getChannelLink ( string $channelLink, string $socialNetwork, Collection $channel ): string
    {
        if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
            return $channelLink;

        return 'https://bsky.app/profile/' . esc_html( $channel->name );
    }

    /**
     * @throws SocialNetworkApiException
     * @throws ChannelSessionException
     */
    public static function refreshChannel (array $updatedChannel, string $socialNetwork, Collection $channel, Collection $channelSession ): array
    {
        if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
            return $updatedChannel;

        $authData = new AuthData();
        $authData->setFromArray( $channelSession->data_obj->auth_data ?? [] );

        $api = new Api();

        $api->setProxy( $channelSession->proxy )
            ->setAuthException( ChannelSessionException::class )
            ->setAuthData( $authData )
            ->createSession();

        $refreshedChannels = ChannelAdapter::fetchChannels( $api );

        foreach ( $refreshedChannels as $refreshedChannel )
        {
            if ( $refreshedChannel[ 'remote_id' ] == $channel->remote_id )
            {
                return $refreshedChannel;
            }
        }

        return $updatedChannel;
    }

    public static function disableSocialChannel ( string $socialNetwork, Collection $channel, Collection $channelSession ): void
    {
        if ( $socialNetwork !== Bootstrap::getInstance()->getSlug() )
            return;

        Channel::where( 'channel_session_id', $channelSession->id )->update( [ 'status' => 0 ] );
    }
}