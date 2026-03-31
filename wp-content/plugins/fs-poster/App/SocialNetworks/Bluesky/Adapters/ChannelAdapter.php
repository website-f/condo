<?php

namespace FSPoster\App\SocialNetworks\Bluesky\Adapters;

use FSPoster\App\Models\Channel;
use FSPoster\App\Models\ChannelSession;
use FSPoster\App\Providers\Channels\ChannelService;
use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\DB\Collection;
use FSPoster\App\Providers\Schedules\SocialNetworkApiException;
use FSPoster\App\SocialNetworks\Bluesky\Api\Api;
use FSPoster\App\SocialNetworks\Bluesky\Api\AuthData;
use FSPoster\App\SocialNetworks\Bluesky\Api\Helpers\Helper;
use FSPoster\App\SocialNetworks\Bluesky\App\Bootstrap;

class ChannelAdapter
{

    /**
     * @param Api $api
     *
     * @return array[]
     * @throws SocialNetworkApiException
     */
    public static function fetchChannels( Api $api ): array
    {
        $profileData = $api->getMe();

        $channelSessionId = ChannelService::addChannelSession( [
            'name'              => $api->authData->identifier,
            'social_network'    => Bootstrap::getInstance()->getSlug(),
            'remote_id'         => $api->authData->did,
            'proxy'             => $api->proxy,
            'data'              => [
                'auth_data' =>  (array)$api->authData,
            ]
        ] );

        $existingChannels = Channel::where( 'channel_session_id', $channelSessionId )
            ->select( [ 'id', 'remote_id' ], true )
            ->fetchAll();

        $existingChannelsIdToRemoteIdMap = [];

        foreach ( $existingChannels as $existingChannel )
        {
            $existingChannelsIdToRemoteIdMap[ $existingChannel->remote_id ] = $existingChannel->id;
        }

        $channelsList = [];
        $channelsList[] = [
            'id'                    => $existingChannelsIdToRemoteIdMap[ $api->authData->did ] ?? null,
            'social_network'        => Bootstrap::getInstance()->getSlug(),
            'name'                  => $api->authData->identifier,
            'channel_type'          => 'account',
            'channel_session_id'    => $channelSessionId,
            'remote_id'             => $api->authData->did,
            'picture'               => $profileData['avatar']
        ];

        return $channelsList;
    }

    public static function updateSessionData(Collection $channelSession, AuthData $authData)
    {
        ChannelSession::where('id', $channelSession->id)
            ->update([
                'data' => json_encode( [
                    'auth_data' => (array)$authData
                ] )
            ]);
    }

    /**
     * @throws ChannelSessionException
     */
    public static function refreshAndUpdateChannelSessionIfNeeded(Collection $channelSession, Api $api)
    {
        if (Helper::isJWTExpired($api->authData->accessJwt, 100)) {
            $newAuthData = $api->refreshSession();
            self::updateSessionData($channelSession, $newAuthData);
        }
    }
}