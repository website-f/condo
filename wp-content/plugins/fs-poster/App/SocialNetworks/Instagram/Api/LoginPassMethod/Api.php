<?php

namespace FSPoster\App\SocialNetworks\Instagram\Api\LoginPassMethod;

use Exception;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\Helpers\GuzzleClient;
use FSPoster\App\Providers\Schedules\ScheduleResponseObject;
use FSPoster\App\SocialNetworks\Instagram\Api\PostingData;
use FSPoster\App\SocialNetworks\Instagram\Helpers\FFmpeg;
use FSPoster\GuzzleHttp\Cookie\CookieJar;
use FSPoster\phpseclib3\Crypt\AES;
use FSPoster\phpseclib3\Crypt\PublicKeyLoader;
use FSPoster\phpseclib3\Crypt\RSA;
use RuntimeException;
use stdClass;

class Api
{

    private array $device = [
        'manufacturer' => 'Samsung',
        'brand'        => 'Samsung',
        'model'        => 'SM-G991B',
        'device'       => 'o1s',
        'android'      => '13',
        'sdk'          => '33',
        'dpi'          => '420dpi',
        'resolution'   => '1080x2400',
        'cpu'          => 'exynos2100'
    ];

    const RESUMABLE_UPLOAD = 1;
    const SEGMENTED_UPLOAD = 2;

	public AuthData $authData;
	public ?string  $proxy = null;

	public string $authException = \Exception::class;
	public string $postException = \Exception::class;

	public function setProxy ( ?string $proxy ): self
	{
		$this->proxy = $proxy;

		return $this;
	}

	public function setAuthData ( AuthData $authData ): self
	{
		$this->authData = $authData;

		if( empty( $this->authData->phone_id ) )
			$this->setPhoneID();

		if( empty( $this->authData->device_id ) )
			$this->setDeviceID();

		if( empty( $this->authData->android_device_id ) )
			$this->setAndroidDeviceID();

		return $this;
	}

	public function setAuthException ( string $exceptionClass ): self
	{
		$this->authException = $exceptionClass;

		return $this;
	}

	public function setPostException ( string $exceptionClass ): self
	{
		$this->postException = $exceptionClass;

		return $this;
	}

    private function getClient () : GuzzleClient
    {
        return new GuzzleClient([
            'proxy' => empty( $this->proxy ) ? null : $this->proxy,
            'verify' => false
        ]);
    }

    private function getDefaultHeaders() : array
    {
        return [
            "User-Agent" => "Instagram 319.0.0.40.107 Android (33/13; 420dpi; 1080x2400; Samsung; SM-G991B; o1s; exynos2100; en_US)",
            "Accept-Encoding" => "gzip, deflate",
            "Accept" => "*/*",
            "Connection" => "keep-alive",
            "X-IG-App-Locale" => "en_US",
            "X-IG-Device-Locale" => "en_US",
            "X-IG-Mapped-Locale" => "en_US",
            "X-Pigeon-Session-Id" => "UFS-" . $this->generateUUID() . "-1",
            "X-Pigeon-Rawclienttime" => sprintf('%.3f', microtime(true)),
            "X-IG-Bandwidth-Speed-KBPS" => sprintf('%.3f', mt_rand(2500000, 3000000)/1000),
            "X-IG-Bandwidth-TotalBytes-B" => (string) mt_rand(5000000, 90000000),
            "X-IG-Bandwidth-TotalTime-MS" => (string) mt_rand(2000, 9000),
            "X-IG-App-Startup-Country" => "US",
            "X-Bloks-Version-Id" => "c5a9c1ecd948577152db1997da9ec137d25e8f0dddc67e7c35d8aee5609ad0da",
            "X-IG-WWW-Claim" => "0",
            "X-Bloks-Is-Layout-RTL" => "false",
            "X-Bloks-Is-Panorama-Enabled" => "true",
            "X-IG-Device-ID" => $this->authData->device_id,
            "X-IG-Family-Device-ID" => $this->authData->phone_id,
            "X-IG-Android-ID" => $this->authData->android_device_id,
            "X-IG-Timezone-Offset" => (string)date('Z'),
            "X-IG-Connection-Type" => "WIFI",
            "X-IG-Capabilities" => "3brTvw==",
            "X-IG-App-ID" => "567067343352427",
            "Priority" => "u=3",
            "Accept-Language" => "en-US",
            "X-MID" => $this->authData->mid ?? '',
            "Host" => "i.instagram.com",
            "X-FB-HTTP-Engine" => "Liger",
            "X-FB-Client-IP" => "True",
            "X-FB-Server-Cluster" => "True",
            "IG-INTENDED-USER-ID" => $this->authData->user_id ?? '0',
            "X-IG-Nav-Chain" => "9MV:self_profile:2,ProfileMediaTabFragment:self_profile:3,9Xf:self_following:4",
            "X-IG-SALT-IDS" => (string) mt_rand(1061162222, 1061262222),
            "Authorization" => $this->authData->authorization ?? '',
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8"
        ];
    }

    public function login() : array
    {
        $this->prefill();
        $key = $this->sync();

        if( $key === false )
	        throw new $this->authException( 'Login failed!' );

        $encPass = $this->encPass( $this->authData->pass, $key['key_id'], $key['pub_key'] );

		$countryCodes = [
			'country_code'  => '1',
			'source'        => ['default']
		];

        $data = [
            'jazoest'               => '22578',
            'country_codes'         => [ json_encode($countryCodes, JSON_UNESCAPED_SLASHES) ],
            'phone_id'              => $this->authData->phone_id,
            'enc_password'          => $encPass,
            'username'              => $this->authData->username,
            'adid'                  => $this->generateUUID(),
            'guid'                  => $this->authData->device_id,
            'device_id'             => $this->authData->android_device_id,
            'google_tokens'         => '[]',
            'login_attempt_count'   => 0,
        ];

        try
        {
            $resp = $this->getClient()->post('https://i.instagram.com/api/v1/accounts/login/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data, JSON_UNESCAPED_SLASHES)
                ]
            ]);
        }
        catch ( Exception $e )
        {
            throw new $this->authException( 'Login failed!' );
        }

        $respArr = json_decode( $resp->getBody()->getContents(), true );

        if( isset($respArr['logged_in_user']['pk_id']) && !empty($resp->getHeader('ig-set-authorization')[0]) )
        {
            $this->authData->authorization = $resp->getHeader('ig-set-authorization')[0];
            $this->authData->user_id = $respArr['logged_in_user']['pk_id'];

            $this->sendPostLoginFlow();

            return [
                'status' => true,
                'data'   => [
                    'needs_challenge' => false,
                    'name'            => empty($respArr['logged_in_user']['full_name']) ? $this->authData->username : $respArr['logged_in_user']['full_name'],
                    'username'        => $this->authData->username,
                    'profile_id'      => $respArr['logged_in_user']['pk_id'],
                    'profile_pic'     => $respArr['logged_in_user']['profile_pic_url'],
                    'options'         => [
						'auth_data'         => (array)$this->authData
                    ]
                ]
            ];
        }
        else
		{
            if( ! isset( $respArr[ 'two_factor_info' ] ) )
				throw new $this->authException( $respArr[ 'message' ] ?? 'Login failed!' );

			$this->authData->user_id = $respArr['two_factor_info']['pk'];

            $verification_method = '1';

            if ( $respArr['two_factor_info']['whatsapp_two_factor_on'] )
                $verification_method = '6';

            if ( $respArr['two_factor_info']['totp_two_factor_on'] )
                $verification_method = '3';

            return [
                'status' => true,
                'data'   => [
                    'needs_challenge' => true,
                    'options'         => [
                        'auth_data'                 => (array)$this->authData,
                        'verification_method'       => $verification_method,
                        'two_factor_identifier'     => $respArr['two_factor_info']['two_factor_identifier'],
                        'obfuscated_phone_number'   => $respArr['two_factor_info']['obfuscated_phone_number'] ?? ( $respArr['two_factor_info']['obfuscated_phone_number_2'] ?? '' )
                    ]
                ]
            ];
        }
    }

    public function doTwoFactorAuth ( $two_factor_identifier, $code, $verification_method = '1' ) : array
    {
        $code = preg_replace( '/\s+/', '', $code );
        $data = [
            "verification_code"     => $code,
            "phone_id"              => $this->authData->phone_id,
            "_csrftoken"            => $this->generateToken(64),
            "two_factor_identifier" => $two_factor_identifier,
            "username"              => $this->authData->username,
            "trust_this_device"     => "0",
            "guid"                  => $this->authData->device_id,
            "device_id"             => $this->authData->android_device_id,
            "waterfall_id"          => $this->generateUUID(),
            "verification_method"   => $verification_method
        ];

        $client = $this->getClient();

        try
        {
            $resp = $client->post('https://i.instagram.com/api/v1/accounts/two_factor_login/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data, JSON_UNESCAPED_SLASHES)
                ]
            ]);
        }
        catch ( Exception $e )
        {
	        throw new $this->postException( '2FA failed!' );
        }

        $auth = $resp->getHeader( 'ig-set-authorization' );
        $body = json_decode($resp->getBody(), true);

        if( empty($auth[0]) )
			throw new $this->postException( $body[ 'message' ] ?? '2FA failed!' );

        $this->authData->authorization = $auth[0];
        $this->authData->user_id = $body['logged_in_user']['pk_id'];

        $data = [
            'name'              => empty($body['logged_in_user']['full_name']) ? $this->authData->username : $body['logged_in_user']['full_name'],
            'username'          => $this->authData->username,
            'profile_id'        => $body['logged_in_user']['pk_id'],
            'profile_pic'       => $body['logged_in_user']['profile_pic_url'],
            'options'           => [
                'auth_data'     => (array)$this->authData
            ]
        ];

        return [
            'status' => true,
            'data'   => $data
        ];
    }

    public function prefill() : void
    {
        try
        {
            $resp = $this->getClient()->post('https://i.instagram.com/api/v1/accounts/contact_point_prefill/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode( [
                            'phone_id' => $this->authData->phone_id,
                            'usage'    => 'prefill'
                        ] )
                ]
            ]);

            if( ! empty( $resp->getHeader('ig-set-x-mid')[0] ) )
            {
                $this->authData->mid = $resp->getHeader('ig-set-x-mid')[0];
            }
        }
        catch ( Exception $e ) {}
    }

    private function sync()
    {
        try
        {
            $resp = $this->getClient()->get('https://i.instagram.com/api/v1/qe/sync/', [
                'headers' => [
                    'User-Agent' => 'Instagram 319.0.0.40.107 Android (33/13; 420dpi; 1080x2400; Samsung; SM-G991B; o1s; exynos2100; en_US)',
                    'Accept-Encoding' => 'gzip,deflate',
                    'Accept' => '*/*',
                    'Connection' => 'Keep-Alive',
                    'Accept-Language' => 'en-US'
                ],
                'cookies' => CookieJar::fromArray([
                    'csrftoken' => $this->generateToken(32),
                    'ig_did'    => strtoupper($this->generateUUID()),
                    'ig_nrcb'   => '1',
                    'mid'       => $this->generateToken(28)
                ], 'i.instagram.com')
            ]);
        }
        catch ( Exception $e )
        {
            return false;
        }

        foreach ($resp->getHeader('Set-Cookie') as $cookie)
        {
            if(strpos($cookie, 'mid') === 0)
            {
                $mid = explode( ';', $cookie )[0];
                $mid = explode('=', $mid)[1];
                if( ! empty($mid) )
                {
                    $this->authData->mid = $mid;
                }
            }
        }

        if( isset($resp->getHeader('Ig-Set-Password-Encryption-Key-Id')[0], $resp->getHeader('Ig-Set-Password-Encryption-Pub-Key')[0]) )
        {
            return [
                'key_id'  => $resp->getHeader('Ig-Set-Password-Encryption-Key-Id')[0],
                'pub_key' => $resp->getHeader('Ig-Set-Password-Encryption-Pub-Key')[0]
            ];
        }

        return false;
    }

    private function encPass ( $password, $publicKeyId, $publicKey ) : string
    {
        $key  = substr( md5( uniqid( mt_rand() ) ), 0, 32 );
        $iv   = substr( md5( uniqid( mt_rand() ) ), 0, 12 );
        $time = time();

        $rsa          = PublicKeyLoader::loadPublicKey( base64_decode( $publicKey ) );
        $rsa          = $rsa->withPadding( RSA::ENCRYPTION_PKCS1 );
        $encryptedRSA = $rsa->encrypt( $key );

        $aes = new AES( 'gcm' );
        $aes->setNonce( $iv );
        $aes->setKey( $key );
        $aes->setAAD( strval( $time ) );
        $encrypted = $aes->encrypt( $password );

        $payload = base64_encode( "\x01" | pack( 'n', intval( $publicKeyId ) ) . $iv . pack( 's', strlen( $encryptedRSA ) ) . $encryptedRSA . $aes->getTag() . $encrypted );

        return sprintf( '#PWD_INSTAGRAM:4:%s:%s', $time, $payload );
    }

    /**
     * X-IG-Family-Device-ID
     */
    private function setPhoneID()
    {
        $this->authData->phone_id = $this->generateUUID();
    }

    /**
     * X-IG-Android-ID
     */
    private function setAndroidDeviceID()
    {
        $this->authData->android_device_id = 'android-' . strtolower($this->generateToken(20));
    }

    /**
     * X-IG-Android-ID
     */
    private function getAndroidDeviceID() : string
    {
        return $this->authData->android_device_id;
    }

    /**
     * X-IG-Device-ID
     */
    private function setDeviceID()
    {
        $this->authData->device_id = $this->generateUUID();
    }

    private function generateUUID () : string
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
    }

    private function generateToken( $len = 10 ) : string
    {
        $letters = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';

        $token = '';
        mt_srand(time());
        for( $i = 0; $i < $len; $i++ ){
            $token .= $letters[mt_rand()%strlen($letters)];
        }

        return $token;
    }

    private function sendPostLoginFlow ()
    {
        try {
            $syncData = [
                '_uuid' => $this->authData->device_id,
                '_uid' => $this->authData->user_id,
                'id' => $this->authData->user_id,
                '_csrftoken' => $this->generateToken(32),
                'experiments' => 'ig_android_reg_nux_headers_cleanup_universe,ig_android_device_detection_info_upload,ig_android_gmail_oauth_in_reg,ig_android_device_info_foreground_reporting,ig_android_device_verification_fb_signup,ig_android_direct_main_tab_universe_v2'
            ];

            $this->getClient()->post('https://i.instagram.com/api/v1/launcher/sync/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($syncData, JSON_UNESCAPED_SLASHES)
                ]
            ]);

            $this->getClient()->post('https://i.instagram.com/api/v1/qe/sync/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode([
                            '_uuid' => $this->authData->device_id,
                            '_uid' => $this->authData->user_id,
                            'id' => $this->authData->user_id,
                            '_csrftoken' => $this->generateToken(32)
                        ], JSON_UNESCAPED_SLASHES)
                ]
            ]);

        } catch (\Exception $e) {
        }
    }

    public function fetchChannels ()
    {
        // TODO: Implement fetchChannels() method.
    }


    public function getMe () : array
    {
        try
        {
            $userBio = (string) $this->getClient()->get( 'https://i.instagram.com/api/v1/accounts/current_user/?edit=true', [
                'headers' => $this->getDefaultHeaders()
            ])->getBody();
        }
        catch (Exception $e)
        {
            throw new $this->authException( $e->getMessage() );
        }

        $userBio = json_decode( $userBio, true );
        if ( ! $userBio || empty( $userBio['user'] ) )
            throw new $this->authException();

        return [
	        'id'                    => $this->authData->user_id,
	        'name'                  => $userBio['user']['full_name'] ?: $this->authData->username,
            'profile_picture_url'   => $userBio['user']['profile_pic_url'] ?? '',
            'username'              => $this->authData->username
        ];
    }

    public function sendPost( PostingData $postingData ) : ScheduleResponseObject
    {
        if( $postingData->edge === 'story' )
        {
            return $this->sendToStory( $postingData );
        }
        else
        {
            return $this->sendToTimeline( $postingData );
        }
    }

    private function sendToTimeline( PostingData $postingData ) : ScheduleResponseObject
    {
        $snPostResponse = new ScheduleResponseObject();

        if (count( $postingData->uploadMedia ) === 1) {
            if( $postingData->uploadMedia[0]['type'] === 'image' ) {
                $response = $this->uploadPhoto( $postingData->uploadMedia[0], $postingData );
            } else {
                $response = $this->uploadVideo( $postingData->uploadMedia[0], $postingData );
            }
        } else {
            $response = $this->generateAlbum( $postingData );
        }

        if ( isset( $response['pk'] ) && $postingData->pinThePost )
            $this->pinPost( $response[ 'pk' ] );

        $snPostResponse->status = 'success';
        $snPostResponse->remote_post_id = $response[ 'id2' ];
        $snPostResponse->data = [
            'url' => 'https://instagram.com/p/' .  $response[ 'id' ]
        ];

        $ids     = explode( '_', $response[ 'id2' ] );
        $mediaId = count( $ids ) > 1 ? $ids[ 0 ] : $response[ 'id2' ];

        if( ! empty( $mediaId ) && $postingData->edge !== 'story' && ! empty( $postingData->firstComment ) )
	        $this->writeComment( $postingData->firstComment, $mediaId );

        return $snPostResponse;
    }

    private function sendToStory( PostingData $postingData ) : ScheduleResponseObject
    {
        $snPostResponse = new ScheduleResponseObject();

        if ( $postingData->uploadMedia[0]['type'] === 'image' )
        {
            $res = $this->uploadPhoto( $postingData->uploadMedia[0], $postingData );

            $snPostResponse->status = 'success';
            $snPostResponse->remote_post_id = $res['id2'];
            $snPostResponse->data = [
                'url' => 'https://instagram.com/' .  $this->authData->username
            ];

            return $snPostResponse;
        }
        else
        {
            $res = $this->uploadVideo( $postingData->uploadMedia[0], $postingData );

            $snPostResponse->status = 'success';
            $snPostResponse->remote_post_id = $res['id2'];

            $snPostResponse->data = [
                'url' => 'https://instagram.com/p/' .  $res[ 'id' ]
            ];

            return $snPostResponse;
        }
    }

    public function uploadPhoto ( $photo, PostingData $postingData ) : array
    {
        $uploadId = $this->createUploadId();

        $this->uploadIgPhoto( $uploadId, $photo );

        if( $postingData->edge === 'feed' )
        {
            $result = $this->configurePhotoToTimeline( $photo, $uploadId, $postingData );
        }
        else
        {
            $result = $this->configurePhotoToStory( $photo, $uploadId, $postingData );
        }

        return [
            'status' => 'ok',
            'pk'     => empty($result[ 'media' ][ 'pk' ]) ? null : $result[ 'media' ][ 'pk' ],
            'id'     => isset( $result[ 'media' ][ 'code' ] ) ? esc_html( $result[ 'media' ][ 'code' ] ) : '?',
            'id2'    => isset( $result[ 'media' ][ 'id' ] ) ? esc_html( $result[ 'media' ][ 'id' ] ) : '?'
        ];
    }

    public function uploadCarouselItem ( $photo )
    {
        $uploadId = $this->createUploadId();

        $params = [
            'media_type'          => '1',
            'upload_media_height' => (string) $photo[ 'height' ],
            'upload_media_width'  => (string) $photo[ 'width' ],
            'upload_id'           => $uploadId,
        ];

        try
        {
            $response = (string) $this->getClient()->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => array_merge($this->getDefaultHeaders(), [
                    'X-Instagram-Rupload-Params' => json_encode( $this->reorderByHashCode( $params ) ),
                    'X-Entity-Type'              => 'image/jpeg',
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $photo[ 'path' ] ),
                    'Offset'                     => '0',
                    'Content-Type'               => 'application/octet-stream'
                ]),
                'body'    => fopen( $photo[ 'path' ], 'r' )
            ] )->getBody();

			$result = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
			$this->handleError( $e->getMessage() );
        }

	    if ( $result[ 'status' ] !== 'ok' )
		    $this->handleError( $result[ 'message' ] ?? 'Error' );

	    return $result;
    }

    private function uploadVideoForCarousel($video)
    {
        $uploadId = $this->createUploadId();

        $this->uploadIgVideo( $uploadId, $video, 'library' );
        $this->uploadIgPhoto( $uploadId, $video['thumbnail'] );

        return [
            'upload_id' => $uploadId,
        ];
    }

    public function generateAlbum ( PostingData $postingData )
    {
        $body = [
            "caption"                       => $postingData->message,
            "children_metadata"             => [],
            "client_sidecar_id"             => $this->createUploadId(),
            "disable_comments"              => "0",
            "like_and_view_counts_disabled" => false,
            "source_type"                   => "library"
        ];

        foreach ( $postingData->uploadMedia as $medium )
        {
            if ($medium[ 'type' ] === 'image' ) {
                $response = $this->uploadCarouselItem( $medium );
            } else {
                $response = $this->uploadVideoForCarousel( $medium );
            }

            $body[ "children_metadata" ][] = [
                "upload_id" => $response[ 'upload_id' ]
            ];
        }

        try
        {
            $response = (string) $this->getClient()->post( "https://i.instagram.com/api/v1/media/configure_sidecar/", [
                'headers' => array_merge($this->getDefaultHeaders(), [
                    'IG-U-DS-USER-ID' => $this->authData->user_id
                ]),
                'form_params'    => [
                    'signed_body' => 'SIGNATURE.' . json_encode($body)
                ]
            ] )->getBody();

            $result = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
			$this->handleError( $e->getMessage() );
        }

	    if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
			$this->handleError( $result[ 'message' ] ?? 'Error' );

	    return [
		    'status' => 'ok',
		    'pk'     => $result[ 'media' ][ 'pk' ] ?? null,
		    'id'     => $result[ 'media' ][ 'code' ] ?? '?',
		    'id2'    => $result[ 'media' ][ 'id' ] ?? '?'
	    ];
    }

    public function uploadVideo ( $video, PostingData $postingData ) : array
    {
        $uploadId = $this->createUploadId();

        $this->uploadIgVideo( $uploadId, $video, $postingData->edge );
        $this->uploadIgPhoto( $uploadId, $video['thumbnail'] );

        $result = $this->configureVideo( $video, $uploadId, $postingData );

        return [
            'status' => 'ok',
            'pk'     => empty($result[ 'media' ][ 'pk' ]) ? null : $result[ 'media' ][ 'pk' ],
            'id'     => isset( $result[ 'media' ][ 'code' ] ) ? esc_html( $result[ 'media' ][ 'code' ] ) : '?',
            'id2'    => isset( $result[ 'media' ][ 'id' ] ) ? esc_html( $result[ 'media' ][ 'id' ] ) : '?'
        ];
    }

    public function pinPost ( $postID ) : void
    {
        $data = [
            'post_id'    => $postID,
            '_uuid'      => $this->authData->device_id,
            'device_id'  => $this->authData->android_device_id,
            'radio_type' => 'wifi_none'
        ];

        try
        {
            $response = (string) $this->getClient()->post( 'https://i.instagram.com/api/v1/users/pin_timeline_media/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]
            ] )->getBody();
        }
        catch ( Exception $e )
        {
        }
    }

    public function getStats ( string $postId ) : array
    {
        $url = 'https://i.instagram.com/api/v1/media/' . urlencode( $postId ) . '/info/';

        try
        {
            $request = (string) $this->getClient()->get( $url )->getBody();
        }
        catch ( Exception $e )
        {
            return [];
        }

        $commentsLikes = json_decode( $request, true );

	    return [
		    [
			    'label' => fsp__( 'Comments' ),
			    'value' => $commentsLikes['items'][0]['comments_count'] ?? 0
		    ],
		    [
			    'label' => fsp__( 'Likes' ),
			    'value' => $commentsLikes['items'][0]['like_count'] ?? 0
		    ],
	    ];
    }

    private function reorderByHashCode ( $data )
    {
        $hashCodes = [];
        foreach ( $data as $key => $value )
        {
            $hashCodes[ $key ] = $this->hashCode( $key );
        }

        uksort( $data, function ( $a, $b ) use ( $hashCodes ) {
            $a = $hashCodes[ $a ];
            $b = $hashCodes[ $b ];
            if ( $a < $b )
            {
                return -1;
            }
            else if ( $a > $b )
            {
                return 1;
            }
            else
            {
                return 0;
            }
        } );

        return $data;
    }

    private function hashCode ( $string ) : int
    {
        $result = 0;
        for ( $i = 0, $len = strlen( $string ); $i < $len; ++$i )
        {
            $result = ( -$result + ( $result << 5 ) + ord( $string[ $i ] ) ) & 0xFFFFFFFF;
        }

        if ( PHP_INT_SIZE > 4 )
        {
            if ( $result > 0x7FFFFFFF )
            {
                $result -= 0x100000000;
            }
            else if ( $result < -0x80000000 )
            {
                $result += 0x100000000;
            }
        }

        return $result;
    }

    private function uploadIgPhoto ( $uploadId, $photo )
    {
        $params = [
            'media_type'          => '1',
            'upload_media_height' => (string) $photo[ 'height' ],
            'upload_media_width'  => (string) $photo[ 'width' ],
            'upload_id'           => $uploadId,
            'image_compression'   => '{"lib_name":"moz","lib_version":"3.1.m","quality":"87"}',
            'xsharing_user_ids'   => '[]',
            'retry_context'       => json_encode( [
                'num_step_auto_retry'   => 0,
                'num_reupload'          => 0,
                'num_step_manual_retry' => 0
            ] )
        ];

        $entity_name = sprintf( '%s_%d_%d', $uploadId, 0, $this->hashCode( basename( $photo[ 'path' ] ) ) );
        $endpoint    = 'https://i.instagram.com/rupload_igphoto/' . $entity_name;

        try
        {
            $response = (string) $this->getClient()->post( $endpoint, [
                'headers' => array_merge($this->getDefaultHeaders(), [
                    'X_FB_PHOTO_WATERFALL_ID'    => $this->generateUUID(),
                    'X-Instagram-Rupload-Params' => json_encode( $this->reorderByHashCode( $params ) ),
                    'X-Entity-Type'              => 'image/jpeg',
                    'X-Entity-Name'              => $entity_name,
                    'X-Entity-Length'            => filesize( $photo[ 'path' ] ),
                    'Offset'                     => '0',
                    'Content-Type'               => 'application/octet-stream'
                ]),
                'body'    => fopen( $photo[ 'path' ], 'r' )
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
            throw new $this->postException( $e->getMessage() );
        }

        return $response;
    }

    private function configurePhotoToTimeline ( $photo, $uploadId, PostingData $postingData )
    {
        $date = date('Ymd\THis.000\Z', time());

        $sendData = [
            '_uuid'                     => $this->authData->device_id,
            'device_id'                 => $this->authData->android_device_id,
            'timezone_offset'           => date('Z'),
            'camera_model'              => $this->device['model'],
            'camera_make'               => $this->device['manufacturer'],
            'scene_type'                => '?',
            'nav_chain'                 => '8rL:self_profile:4,ProfileMediaTabFragment:self_profile:5,UniversalCreationMenuFragment:universal_creation_menu:7,ProfileMediaTabFragment:self_profile:8,MediaCaptureFragment:tabbed_gallery_camera:9,Dd3:photo_filter:10,FollowersShareFragment:metadata_followers_share:11',
            'date_time_original'        => $date,
            'date_time_digitalized'     => $date,
            'creation_logger_session_id'=> $this->generateUUID(),
            'scene_capture_type'        => 'standard',
            'software'                  => 'SM-G991B-user+13+TP1A.220624.014+G991BXXU6EWAF+release-keys',
            'multi_sharing'             => '1',
            'location'                  => json_encode(new stdClass()),
            'usertags'                  => json_encode(['in' => []]),
            'edits'                     => [
                'crop_original_size'    => [(float)$photo['width'], (float)$photo['height']],
                'crop_zoom'             => 1.0,
                'crop_center'           => [0.0, -0.0]
            ],
            'extra'                     => [
                'source_width'          => (float) $photo['width'],
                'source_height'         => (float) $photo['height'],
            ],
            'upload_id'                 => $uploadId,
            'device'                    => $this->device,
            'caption'                   => $postingData->message,
            'source_type'               => '4',
            'media_folder'              => 'Camera',
        ];

        try
        {
            $c = $this->getClient();
            $response = (string) $c->post( 'https://i.instagram.com/api/v1/media/configure/', [
                'headers' => array_merge($this->getDefaultHeaders(), [
                    'IG-U-DS-USER-ID' => $this->authData->user_id
                ]),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($sendData)
                ]
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
            $this->handleError( $e->getMessage() );
        }

	    if ( isset( $response[ 'status' ] ) && $response[ 'status' ] == 'fail' )
		    $this->handleError( $response[ 'message' ] ?? 'Error' );

        return $response;
    }

    private function configurePhotoToStory ( $photo, $uploadId, PostingData $postingData )
    {
        $tap_models = '}';

        if ( ! empty( $postingData->link ) )
        {
            sleep(2);

            try {
                $validateData = [
                    'url' => $postingData->link,
                    '_uid' => $this->authData->user_id,
                    '_uuid' => $this->authData->device_id,
                    '_csrftoken' => $this->generateToken(32),
                    'device_id' => $this->authData->android_device_id
                ];

                $this->getClient()->post('https://i.instagram.com/api/v1/media/validate_reel_url/', [
                    'headers' => array_merge($this->getDefaultHeaders(), [
                        'IG-U-DS-USER-ID' => $this->authData->user_id
                    ]),
                    'form_params' => [
                        'signed_body' => 'SIGNATURE.' . json_encode($validateData, JSON_UNESCAPED_SLASHES)
                    ]
                ]);
            } catch (\Exception $e) {
            }

            $link_y = $postingData->linkConfig['top_offset'];
            $link_y = $link_y / $photo[ 'height' ];

            $link_model = '{\"x\":0.5126011,\"y\":' . $link_y . ',\"z\":0,\"width\":0.80998676,\"height\":0.12075,\"rotation\":0.0,\"type\":\"story_link\",\"is_sticker\":true,\"selected_index\":0,\"tap_state\":0,\"link_type\":\"web\",\"url\":\"' . $postingData->link . '\",\"tap_state_str_id\":\"link_sticker_default\"}';
        }

        if ( ! empty( $postingData->storyHashtag ) )
        {
            $hashtag_y = $postingData->storyHashtagConfig['top_offset'];
            $hashtag_y     = $hashtag_y / $photo[ 'height' ];
            $hashtag_y     = number_format( $hashtag_y, 2 );
            $hashtag_model = '{\"x\":0.51,\"y\":' . $hashtag_y . ',\"z\":0,\"width\":0.8,\"height\":0.12,\"rotation\":0.0,\"type\":\"hashtag\",\"tag_name\":\"' . $postingData->storyHashtag . '\",\"is_sticker\":true,\"tap_state\":0,\"tap_state_str_id\":\"hashtag_sticker_gradient\"}';
        }

        if ( ! empty( $hashtag_model ) || ! empty( $link_model ) )
        {
            $tap_models = ! empty( $hashtag_model ) && ! empty( $link_model ) ? ( $hashtag_model . ',' . $link_model ) : ( empty( $link_model ) ? $hashtag_model : $link_model );
            $tap_models = ',"tap_models":"[' . $tap_models . ']"}';
        }

        sleep(1);

        try
        {
            $response = (string) $this->getClient()->post( 'https://i.instagram.com/api/v1/media/configure_to_story/', [
                'headers'     => array_merge($this->getDefaultHeaders(), [
                    'IG-U-DS-USER-ID' => $this->authData->user_id
                ]),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.{"_uuid":"' . $this->authData->device_id . '","device_id":"' . $this->authData->android_device_id . '","text_metadata":"[{\"font_size\":40.0,\"scale\":1.0,\"width\":611.0,\"height\":169.0,\"x\":0.51414347,\"y\":0.8487708,\"rotation\":0.0}]","supported_capabilities_new":"[{\"name\":+\"SUPPORTED_SDK_VERSIONS\",+\"value\":+\"108.0,109.0,110.0,111.0,112.0,113.0,114.0,115.0,116.0,117.0,118.0,119.0,120.0,121.0,122.0,123.0,124.0,125.0,126.0,127.0,128.0,129.0,130.0,131.0,132.0,133.0,134.0,135.0,136.0,137.0,138.0,139.0,140.0\"},+{\"name\":+\"FACE_TRACKER_VERSION\",+\"value\":+\"14\"},+{\"name\":+\"segmentation\",+\"value\":+\"segmentation_enabled\"},+{\"name\":+\"COMPRESSION\",+\"value\":+\"ETC2_COMPRESSION\"},+{\"name\":+\"world_tracker\",+\"value\":+\"world_tracker_enabled\"},+{\"name\":+\"gyroscope\",+\"value\":+\"gyroscope_enabled\"}]","has_original_sound":"1","camera_session_id":"45e0c374-d84f-4289-9f81-a7419752f684","scene_capture_type":"","timezone_offset":"-14400","client_shared_at":"' . ( time() - 5 ) . '","story_sticker_ids":"link_sticker_default","media_folder":"Camera","configure_mode":"1","source_type":"4","creation_surface":"camera","imported_taken_at":1643659109,"capture_type":"normal","rich_text_format_types":"[\"default\"]","upload_id":"' . $uploadId . '","client_timestamp":"' . time() . '","device":{"android_version":33,"android_release":"13","manufacturer":"Samsung","model":"SM-G991B"},"_uid":49154269846,"composition_id":"8e56be0b-ba75-44c6-bd61-9fd77680f84a","app_attribution_android_namespace":"","media_transformation_info":"{\"width\":\"720\",\"height\":\"720\",\"x_transform\":\"0\",\"y_transform\":\"0\",\"zoom\":\"1.0\",\"rotation\":\"0.0\",\"background_coverage\":\"0.0\"}","original_media_type":"photo","camera_entry_point":"121","edits":{"crop_original_size":[720.0,720.0],"filter_type":0,"filter_strength":1.0},"extra":{"source_width":720,"source_height":720}' . $tap_models
                ]
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
            $this->handleError( $e->getMessage() );
        }

	    if ( isset( $response[ 'status' ] ) && $response[ 'status' ] == 'fail' )
		    $this->handleError( $response[ 'message' ] ?? 'Error' );

        return $response;
    }

    private function uploadIgVideo ( $uploadId, $video, $target = 'feed' )
    {
        $uploadMethod = static::RESUMABLE_UPLOAD;

        if ( $target == 'story' || $target == 'library' || $video[ 'duration' ] > 10 )
            $uploadMethod = static::SEGMENTED_UPLOAD;

        if ( $uploadMethod === static::RESUMABLE_UPLOAD )
            $response = $this->uploadIgVideoResumableMethod( $uploadId, $video, $target );
        else
            $response = $this->uploadIgVideoSegmentedMethod( $uploadId, $video, $target );

        return $response;
    }

    private function uploadIgVideoResumableMethod ( $uploadId, $video, $target )
    {
        $params = [
            'upload_id'                => $uploadId,
            'retry_context'            => json_encode( [
                'num_step_auto_retry'   => 0,
                'num_reupload'          => 0,
                'num_step_manual_retry' => 0
            ] ),
            'xsharing_user_ids'        => '[]',
            'upload_media_height'      => (string) $video[ 'height' ],
            'upload_media_width'       => (string) $video[ 'width' ],
            'upload_media_duration_ms' => (string) $video[ 'duration' ] * 1000,
            'media_type'               => '2',
            'potential_share_types'    => json_encode( [ 'not supported type' ] ),
        ];

        if ( $target == 'story' )
        {
            $params[ 'for_album' ] = '1';
        }
        if ( $target == 'library' )
        {
            $params[ 'is_sidecar' ] = '1';
        }


        $entity_name = sprintf( '%s_%d_%d', $uploadId, 0, $this->hashCode( basename( $video[ 'path' ] ) ) );

        try
        {
            $response = (string) $this->getClient()->post( 'https://i.instagram.com/rupload_igvideo/' . $entity_name, [
                'headers' => array_merge($this->getDefaultHeaders(), [
                    'X_FB_VIDEO_WATERFALL_ID'    => $this->generateUUID(),
                    'X-Instagram-Rupload-Params' => json_encode( $this->reorderByHashCode( $params ) ),
                    'X-Entity-Type'              => 'video/mp4',
                    'X-Entity-Name'              => $entity_name,
                    'X-Entity-Length'            => filesize( $video[ 'path' ] ),
                    'Offset'                     => '0'
                ]),
                'body'    => fopen( $video[ 'path' ], 'r' )
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
            $response = [];
        }

        return $response;
    }

    private function uploadIgVideoSegmentedMethod ( $uploadId, $video, $target ) : array
    {
        $videoSegments = $this->splitVideoSegments( $video, $target );

        $params = [
            'upload_id'                => $uploadId,
            'retry_context'            => json_encode( [
                'num_step_auto_retry'   => 0,
                'num_reupload'          => 0,
                'num_step_manual_retry' => 0
            ] ),
            'xsharing_user_ids'        => '[]',
            'upload_media_height'      => (string) $video[ 'height' ],
            'upload_media_width'       => (string) $video[ 'width' ],
            'upload_media_duration_ms' => (string) $video[ 'duration' ] * 1000,
            'media_type'               => '2',
            'potential_share_types'    => json_encode( [ 'not supported type' ] ),
        ];

        if ( $target == 'story' )
        {
            $params[ 'for_album' ] = '1';
        }
        if ( $target == 'library' )
        {
            $params[ 'is_sidecar' ] = '1';
        }

        $startRequest = $this->getClient()->post( 'https://i.instagram.com/rupload_igvideo/' . $this->generateUUID() . '?segmented=true&phase=start', [
            'headers' => array_merge($this->getDefaultHeaders(), [
                'X-Instagram-Rupload-Params' => json_encode( $this->reorderByHashCode( $params ) )
            ])
        ] )->getBody();

        $startRequest = json_decode( $startRequest, true );

        $streamId = $startRequest[ 'stream_id' ];

        $offset      = 0;
        $waterfallId = $this->createUploadId();

        foreach ( $videoSegments as $segment )
        {
            $segmentSize = filesize( $segment );
            $isAudio     = preg_match( '/audio\.mp4$/', $segment );

            $headers = [
                'Segment-Start-Offset'       => $offset,
                'Segment-Type'               => $isAudio ? 1 : 2,
                'Stream-Id'                  => $streamId,
                'X_FB_VIDEO_WATERFALL_ID'    => $waterfallId,
                'X-Instagram-Rupload-Params' => json_encode( $this->reorderByHashCode( $params ) )
            ];

            $entity_name = md5( $segment ) . '-0-' . $segmentSize;

            $getOffset = $this->getClient()->get( 'https://i.instagram.com/rupload_igvideo/' . $entity_name . '?segmented=true&phase=transfer', [
                'headers' => array_merge($this->getDefaultHeaders(), $headers)
            ] )->getBody();

            $getOffset = json_decode( $getOffset, true );

            $headers[ 'X-Entity-Type' ]   = 'video/mp4';
            $headers[ 'X-Entity-Name' ]   = $entity_name;
            $headers[ 'X-Entity-Length' ] = $segmentSize;
            $headers[ 'Offset' ]          = isset( $getOffset[ 'offset' ] ) ? (int) $getOffset[ 'offset' ] : 0;

            $this->getClient()->post('https://i.instagram.com/rupload_igvideo/' . $entity_name . '?segmented=true&phase=transfer', [
                'headers' => array_merge($this->getDefaultHeaders(), $headers),
                'body'    => fopen( $segment, 'r' ),
            ] )->getBody();

            $offset += $segmentSize;
        }

        $startRequest = $this->getClient()->post('https://i.instagram.com/rupload_igvideo/' . $this->generateUUID() . '?segmented=true&phase=end', [
            'headers' => array_merge($this->getDefaultHeaders(), [
                'Stream-Id'                  => $streamId,
                'X-Instagram-Rupload-Params' => json_encode( $this->reorderByHashCode( $params ) )
            ])
        ] )->getBody();

        $this->unlinkSegments( $videoSegments );

        return [];
    }

    private function splitVideoSegments( array $video, string $target ) : array
    {
        $segmentTime = $target === 'story' ? 2 : 5;

        $segmentId = md5(
            $video['path'] .
            microtime(true) .
            random_bytes(8)
        );

        $tempDir = WP_CONTENT_DIR . '/uploads/fs-poster-tmp';

        if ( ! is_dir( $tempDir ) )
        {
            wp_mkdir_p( $tempDir );
        }

        if ( ! is_writable( $tempDir ) )
        {
            throw new RuntimeException('FS Poster temp directory is not writable');
        }

        $segmentsPath         = $tempDir . DIRECTORY_SEPARATOR . 'fs_' . $segmentId . '_%03d.mp4';
        $segmentsPathForAudio = $tempDir . DIRECTORY_SEPARATOR . 'fs_' . $segmentId . '_audio.mp4';

        $ffmpeg = FFmpeg::factory();

        try
        {
            $ffmpeg->run([
                '-y',
                '-i', $video['path'],
                '-map', '0:v:0',
                '-c:v', 'libx264',
                '-preset', 'fast',
                '-g', '30',
                '-sc_threshold', '0',
                '-an',
                '-f', 'segment',
                '-segment_time', (string) $segmentTime,
                '-reset_timestamps', '1',
                $segmentsPath
            ]);

            if ( ! empty( $video['audio_codec'] ) )
            {
                $ffmpeg->run([
                    '-y',
                    '-i', $video['path'],
                    '-map', '0:a:0',
                    '-c:a', 'copy',
                    '-vn',
                    $segmentsPathForAudio
                ]);
            }
        }
        catch ( RuntimeException $e )
        {
            $segments = $this->findSegments( $segmentId, $tempDir );
            $this->unlinkSegments( $segments );
            throw $e;
        }

        $segments = $this->findSegments( $segmentId, $tempDir );

        if ( empty( $segments ) )
        {
            throw new RuntimeException('FFmpeg did not generate any video segments');
        }

        return $segments;
    }


    private function findSegments( string $segmentId, string $tempDir ) : array
    {
        $segmentsPath      = $tempDir . DIRECTORY_SEPARATOR . 'fs_' . $segmentId . '_*.mp4';
        $segmentsPathAudio = $tempDir . DIRECTORY_SEPARATOR . 'fs_' . $segmentId . '_audio.mp4';

        $result = glob( $segmentsPath ) ?: [];

        if ( is_file( $segmentsPathAudio ) )
        {
            $result[] = $segmentsPathAudio;
        }

        return $result;
    }

    private function unlinkSegments( array $segments ) : void
    {
        foreach ( $segments as $file_path )
        {
            unlink( $file_path );
        }
    }

    private function configureVideo ( $video, $uploadId, PostingData $postingData )
    {
        $sendData = [
            'supported_capabilities_new' => json_encode( [
                [
                    'name'  => 'SUPPORTED_SDK_VERSIONS',
                    'value' => '108.0,109.0,110.0,111.0,112.0,113.0,114.0,115.0,116.0,117.0,118.0,119.0,120.0,121.0,122.0,123.0,124.0,125.0,126.0,127.0,128.0,129.0,130.0,131.0,132.0,133.0,134.0,135.0,136.0,137.0,138.0,139.0,140.0'
                ],
                [ 'name' => 'FACE_TRACKER_VERSION', 'value' => '14' ],
                [ 'name' => 'segmentation', 'value' => 'segmentation_enabled' ],
                [ 'name' => 'COMPRESSION', 'value' => 'ETC2_COMPRESSION' ],
                [ 'name' => 'world_tracker', 'value' => 'world_tracker_enabled' ],
                [ 'name' => 'gyroscope', 'value' => 'gyroscope_enabled' ]
            ] ),
            'video_result'               => '',
            'upload_id'                  => $uploadId,
            'poster_frame_index'         => 0,
            'length'                     => round( $video[ 'duration' ], 1 ),
            'audio_muted'                => false,
            'filter_type'                => 0,
            'source_type'                => 4,
            'device'                     => [
                'android_version' => 33,
                'android_release' => '13',
                'manufacturer'    => $this->device['manufacturer'],
                'model'           => $this->device['model']
            ],
            'extra'                      => [
                'source_width'  => $video[ 'width' ],
                'source_height' => $video[ 'height' ],
            ],
            '_csrftoken'                 => $this->generateToken(32),
            '_uid'                       => $this->authData->user_id,
            '_uuid'                      => $this->authData->device_id,
            'caption'                    => $postingData->message
        ];

        switch ( $postingData->edge )
        {
            case 'story':
                $endpoint = 'media/configure_to_story/';

                $sendData[ 'configure_mode' ]            = 1;
                $sendData[ 'story_media_creation_date' ] = time() - mt_rand( 10, 20 );
                $sendData[ 'client_shared_at' ]          = time() - mt_rand( 3, 10 );
                $sendData[ 'client_timestamp' ]          = time();

                if ( ! empty( $postingData->link ) )
                {
                    $sendData[ 'story_cta' ] = '[{"links":[{"linkType": 1, "webUri":' . json_encode( $postingData->link ) . ', "androidClass": "", "package": "", "deeplinkUri": "", "callToActionTitle": "", "redirectUri": null, "leadGenFormId": "", "igUserId": "", "appInstallObjectiveInvalidationBehavior": null}]}]';
                }
                break;
            default:
                $endpoint = 'media/configure/';

                $sendData[ 'caption' ] = $postingData->message;
        }

        sleep(2);
        try
        {
            $response = (string) $this->getClient()->post( 'https://i.instagram.com/api/v1/' . $endpoint . '?video=1', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($sendData)
                ]
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
            $this->handleError( $e->getMessage() );
        }

	    if ( isset( $response[ 'status' ] ) && $response[ 'status' ] == 'fail' )
			$this->handleError( $response[ 'message' ] ?? 'Error' );

        return $response;
    }

    private function createUploadId () : string
    {
        return number_format( round( microtime( true ) * 1000 ), 0, '', '' );
    }

    public function writeComment ( $comment, $mediaId ) : string
    {
        $data = [
            "_uuid"             => $this->authData->device_id,
            "device_id"         => $this->authData->android_device_id,
            "delivery_class"    => "organic",
            "feed_position"     => "0",
            "container_module"  => "self_comments_v2_feed_contextual_self_profile",
            "comment_text"      => $comment,
            'idempotence_token' => $this->generateUUID()
        ];

        $endpoint = sprintf( "https://i.instagram.com/api/v1/media/%s/comment/", $mediaId );

        try
        {
            $response = (string) $this->getClient()->post( $endpoint, [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
	        throw new $this->postException( 'First comment error: ' . $e->getMessage() );
        }

        if ( ! isset( $response[ 'comment' ][ 'pk' ] ) )
	        throw new $this->postException( 'First comment error: ' . ($response[ 'message' ] ?? 'Unknown error') );

        return (string)$response[ 'comment' ][ 'pk' ];
    }

    private function handleError ( $errorMsg = null )
    {
        if ( $errObj = json_decode( $errorMsg, true ) )
        {
            $errorMsg = $errObj[ 'message' ] ?? $errorMsg;

            if ( isset( $errObj['checkpoint_url'] ) &&
                strpos( $errObj['checkpoint_url'], 'unsupported_version' ) !== false )
            {
                throw new $this->authException(
                    'Instagram app version is outdated and no longer supported. ' .
                    'Please re-add your Instagram account to refresh the connection with the latest version. ' .
                    'If the issue persists, contact support for an update.'
                );
            }

            if ( $errorMsg === 'checkpoint_required' ||
                isset( $errObj['challenge'] ) ||
                ( isset( $errObj['status'] ) && $errObj['status'] === 'fail' &&
                    isset( $errObj['error_type'] ) && $errObj['error_type'] === 'checkpoint_challenge_required' ) )
            {
                $checkpointUrl = $errObj['checkpoint_url'] ?? '';
                throw new $this->authException(
                    'Instagram requires verification. Please:' . "\n" .
                    '1. Log into Instagram on your browser or app' . "\n" .
                    '2. Complete any security verification requested' . "\n" .
                    '3. Wait 15-30 minutes before trying again' . "\n\n" .
                    'This is a temporary security measure by Instagram.' .
                    ( $checkpointUrl ? "\nCheckpoint URL: " . $checkpointUrl : '' )
                );
            }
        }

        $errorMsg = $errorMsg ?: 'An error occurred while processing the request';

		if ( $errorMsg === 'login_required' )
			throw new $this->authException( 'The account is disconnected from the plugin. Please add your account to the plugin again by getting the cookie on the browser <a href=\'https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-instagram\' target=\'_blank\'>Incognito mode</a>. And close the browser without logging out from the account.' );
		else
			throw new $this->postException( $errorMsg );
	}

}