<?php

namespace FSPoster\App\SocialNetworks\Twitter\Api\CookieMethod;

use FSPoster\App\Providers\Helpers\Helper;
use FSPoster\App\SocialNetworks\Twitter\Api\PostingData;
use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Cookie\CookieJar;
use FSPoster\GuzzleHttp\Cookie\SetCookie;
use FSPoster\GuzzleHttp\Exception\GuzzleException;
use Exception;

class Api
{

    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:147.0) Gecko/20100101 Firefox/147.0';

	public AuthData $authData;
	public ?string  $proxy = null;

	public string $authException = \Exception::class;
	public string $postException = \Exception::class;

	private ?string $csrfToken = null;

	private string $bearerToken = 'AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA';

	private ?string $homePageHtml = null;
    private CookieJar $cookieJar;
    private ?string $xxpff = null;
    private ?CloudflareWorker $worker = null;

    public function initWorker(WorkerCredentialsDTO $credentials): self
    {
        $this->worker = (new CloudflareWorker($credentials))
            ->setProxy($this->proxy);

        return $this;
    }

	public function setProxy ( ?string $proxy ): self
	{
		$this->proxy = $proxy;

		return $this;
	}

	public function setAuthData ( AuthData $authData ): self
	{
		$this->authData = $authData;
        $this->initialiseCookieJar();

		return $this;
	}

    private function initialiseCookieJar (): void
    {
        $defaultCookies = [
            'dnt' => '1',
            'des_opt_in' => 'Y',
        ];

        if (! empty($this->authData->authToken)) {
            $defaultCookies['auth_token'] = $this->authData->authToken;
        }

        $this->cookieJar = new CookieJar();
        foreach($defaultCookies as $name => $value) {
            $this->cookieJar->setCookie(new SetCookie([
                'Name' => $name,
                'Value' => $value,
                'Domain' => '.x.com',
                'Path' => '/',
            ]));
        }
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

	private function getDefaultHeaders(): array
    {
        return [
            'user-agent'        => static::USER_AGENT,
            'content-type'      => 'application/json',
            'Origin'            => 'https://x.com',
            'Referer'           => 'https://x.com/home',
            'accept'            => '*/*',
            'accept-language'   => 'en-US,en;q=0.9',
            'accept-encoding'   => 'gzip, deflate, br, zstd',
            'sec-fetch-dest'    => 'empty',
            'sec-fetch-mode'    => 'cors',
            'sec-fetch-site'    => 'same-origin',
            'te'                => 'trailers',
        ];
    }

    private function getApiHeaders(array $extraHeaders = []): array
    {
        $headers = array_merge($this->getDefaultHeaders(), [
            'authorization' => 'Bearer ' . $this->bearerToken,
            'x-twitter-auth-type' => 'OAuth2Session',
            'x-twitter-client-language' => 'en',
            'x-twitter-active-user' => 'yes',
        ]);

        $xxpffHeader = $this->getXXPFF();
        if (!empty($xxpffHeader)) {
            $headers['x-xp-forwarded-for'] = $xxpffHeader;
        }

        if (!empty($this->csrfToken)) {
            $headers['x-csrf-token'] = $this->csrfToken;
        }

        return array_merge($headers, $extraHeaders);
    }

    private function getCookieString(): string
    {
        return implode('; ', array_map(function($cookie) {
            return $cookie['Name'] . '=' . $cookie['Value'];
        }, $this->cookieJar->toArray()));
    }

    private function getClient(array $headers = []): Client
	{
        $headers = array_merge($this->getDefaultHeaders(), $headers);

		return new Client([
            'verify'        => false,
            'http_errors'   => false,
            'proxy'         => empty( $this->proxy ) ? null : $this->proxy,
            'version'       => '2.0',
            'headers'       => $headers,
            'cookies'       => $this->cookieJar,
        ]);
	}

    private function findCookieInJar(string $cookieName): ?string
    {
        foreach ($this->cookieJar->toArray() as $cookie) {
            if ($cookie['Name'] !== $cookieName) {
                continue;
            }

            return $cookie['Value'];
        }

        return null;
    }

    private function getApiClient(): Client
    {
        return $this->getClient($this->getApiHeaders());
    }

    /**
     * @throws \JsonException
     */
    private function getXXPFF(): ?string
    {
        if (empty($this->xxpff)) {
            $guestIdCookie = $this->findCookieInJar('guest_id');

            if (empty($guestIdCookie)) {
                return null;
            }

            $xPFFHeaderGenerator = new XPFFHeaderGenerator();

            $this->xxpff = $xPFFHeaderGenerator->generateXPFF(static::USER_AGENT, $guestIdCookie);
        }

        return $this->xxpff;
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function sendPost (PostingData $postingData ) : string
    {
        $this->requestToHomePage();

		$sendMedia = [];
		$message = $postingData->message;

		if ( ! empty( $postingData->link ) ) {
            $message .= "\n" . $postingData->link;
        }

		if ( ! empty( $postingData->uploadMedia ) )
		{
			foreach ( $postingData->uploadMedia as $c => $media )
			{
				if ( $c > 3 ) {
                    break;
                }

				$mediaType = $media['type'] === 'video' ? 'tweet_video' : 'tweet_image';

				$mediaId = $this->uploadMedia( $media['path'], $mediaType );

				if ( ! empty( $mediaId ) )
				{
					$sendMedia[] = [
						'media_id'     => $mediaId,
						'tagged_users' => []
					];
				}
			}
		}

        $postId = $this->createTweet( $message, $sendMedia );

		if ( ! empty( $postingData->firstComment ) ) {
            $this->createTweet($postingData->firstComment, [], $postId);
        }

		return (string)$postId;
	}

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function createTweet ($content, $mediaList, $replyMediaId = false) : string
    {
		$queryId = 'f4NGXqNlXoGYCWploMNtlQ';

		$sendData = [
            "features"  => [
                "articles_preview_enabled"                                                  => true,
                "c9s_tweet_anatomy_moderator_badge_enabled"                                 => true,//$this->randomBoolean(),
                "communities_web_enable_tweet_community_results_fetch"                      => true,
                "creator_subscriptions_quote_tweet_preview_enabled"                         => false,
                "freedom_of_speech_not_reach_fetch_enabled"                                 => true,
                "graphql_is_translatable_rweb_tweet_is_translatable_enabled"                => true,
                "longform_notetweets_consumption_enabled"                                   => true,
                "longform_notetweets_inline_media_enabled"                                  => true,
                "longform_notetweets_rich_text_read_enabled"                                => true,
                "post_ctas_fetch_enabled"                                                   => true,
                "premium_content_api_read_enabled"                                          => false,//$this->randomBoolean(),
                "profile_label_improvements_pcf_label_in_post_enabled"                      => true,
                "responsive_web_edit_tweet_api_enabled"                                     => true,
                "responsive_web_enhance_cards_enabled"                                      => false,
                "responsive_web_graphql_skip_user_profile_image_extensions_enabled"         => false,
                "responsive_web_graphql_timeline_navigation_enabled"                        => true,
                "responsive_web_grok_analysis_button_from_backend"                          => true,//$this->randomBoolean(),
                "responsive_web_grok_analyze_button_fetch_trends_enabled"                   => false,//$this->randomBoolean(),
                "responsive_web_grok_analyze_post_followups_enabled"                        => true,//??$this->randomBoolean(),
                "responsive_web_grok_annotations_enabled"                                   => false,//$this->randomBoolean(),
                "responsive_web_grok_community_note_auto_translation_is_enabled"            => false,//$this->randomBoolean(),
                "responsive_web_grok_image_annotation_enabled"                              => true,//$this->randomBoolean(),
                "responsive_web_grok_imagine_annotation_enabled"                            => true,//$this->randomBoolean(),
                "responsive_web_grok_share_attachment_enabled"                              => true,//$this->randomBoolean(),
                "responsive_web_grok_show_grok_translated_post"                             => false,//$this->randomBoolean(),
                "responsive_web_jetfuel_frame"                                              => true,//$this->randomBoolean(),
                "responsive_web_profile_redirect_enabled"                                   => false,//$this->randomBoolean(),
                "responsive_web_twitter_article_tweet_consumption_enabled"                  => true,
                "rweb_tipjar_consumption_enabled"                                           => true,
                "standardized_nudges_misinfo"                                               => true,
                "tweet_awards_web_tipping_enabled"                                          => false,
                "tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled"   => true,
                "verified_phone_label_enabled"                                              => false,
                "view_counts_everywhere_api_enabled"                                        => true,
            ],
			"queryId"   => $queryId,
            "variables" => [
                "dark_request"              => false,
                "disallowed_reply_options"  => null,
                "media"                     => [
                    "media_entities"     => [],
                    "possibly_sensitive" => false
                ],
                "semantic_annotation_ids"   => [],
                "tweet_text"                => $content,
            ],
		];

		if(! empty($mediaList))
		{
			$sendData['variables']['media'] = [
				'media_entities'     => $mediaList,
				'possibly_sensitive' => false
			];
		}

		if( ! empty( $replyMediaId ) )
		{
			$sendData['variables']['reply'] = [
				"exclude_reply_user_ids"    => [],
				"in_reply_to_tweet_id"      => $replyMediaId
			];
		}

		$path = '/i/api/graphql/' . $queryId . '/CreateTweet';
        $transactionId = $this->generateTransactionId('POST', $path);

        $headers = $this->getApiHeaders([
            'x-client-transaction-id' => $transactionId,
            'cookie' => $this->getCookieString(),
        ]);

        $url = 'https://x.com' . $path;
        $body = json_encode($sendData, JSON_THROW_ON_ERROR);

        $workerResult = $this->worker->sendRequest($url, 'POST', $headers, $body);
        $response = $workerResult['body'];
        $resArr = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

		if ( ! is_array( $resArr ) ) {
            throw new $this->postException($response);
        }

		if ( !isset( $resArr['data']['create_tweet']['tweet_results']['result']['rest_id'] ) ) {
            throw new $this->postException($resArr['errors'][0]['message'] ?? fsp__('Unknown error'));
        }

		return (string)$resArr['data']['create_tweet']['tweet_results']['result']['rest_id'];
	}

	private function uploadMedia ( $file, $type )
	{
		try
		{
			$uploadINIT = ( string ) $this->getApiClient()->post( 'https://upload.x.com/i/media/upload.json', [
				'query'   => [
					'command'        => 'INIT',
					'total_bytes'    => filesize( $file ),
					'media_type'     => Helper::mimeContentType( $file ),
					'media_category' => $type
				]
			] )->getBody();
			$uploadINIT = json_decode( $uploadINIT );

			if ( empty( $uploadINIT->media_id ) ) {
                throw new $this->postException();
            }

			$mediaID = $uploadINIT->media_id_string;
		}
		catch ( Exception $e )
		{
			throw new $this->postException( $e->getMessage() );
		}

		try
		{
			$segmentIndex = 0;
			$handle       = fopen( $file, 'rb' );

			if (empty($handle)) {
                throw new $this->postException();
            }

			while ( ! feof( $handle ) )
			{
				$this->getApiClient()->post( 'https://upload.x.com/i/media/upload.json', [
					'query'     => [
						'command'       => 'APPEND',
						'segment_index' => $segmentIndex,
						'media_id'      => $mediaID,
					],
					'multipart' => [
						[
							'name'     => 'media',
							'contents' => fread( $handle, 250000 ),
							'filename' => 'blob',
							'headers'  => [
								'Content-Type' => 'application/octet-stream',
							]
						]
					],
				] );

				$segmentIndex++;
			}

			fclose($handle);
		}
		catch (Exception $e) {
			throw new $this->postException($e->getMessage());
		} catch (GuzzleException $e) {
            throw new $this->postException($e->getMessage());
        }

        try
		{
			$uploadFINALIZE = ( string ) $this->getApiClient()->post( 'https://upload.x.com/i/media/upload.json', [
				'query'   => [
					'command'  => 'FINALIZE',
					'media_id' => $mediaID,
				],
			] )->getBody();

			$uploadFINALIZE = json_decode($uploadFINALIZE, false, 512, JSON_THROW_ON_ERROR);

			if ( empty( $uploadFINALIZE->media_id ) ) {
                throw new $this->postException();
            }

			if ( $type === 'tweet_video' )
			{
				if ( ! empty( $uploadFINALIZE->processing_info->state ) )
				{
					$uploaded = false;

					while ( ! $uploaded )
					{
						$uploadSTATUS = ( string ) $this->getApiClient()->get( 'https://upload.x.com/i/media/upload.json', [
							'query'   => [
								'command'  => 'STATUS',
								'media_id' => $mediaID,
							],
						] )->getBody();
						$uploadSTATUS = json_decode($uploadSTATUS, false, 512, JSON_THROW_ON_ERROR);

						if ( ! empty( $uploadSTATUS->processing_info->state ) && $uploadSTATUS->processing_info->state === 'succeeded' )
						{
							$uploaded = true;
						}

						usleep(0.2 * 1000 * 1000);
					}
				}
				else
				{
					throw new $this->postException();
				}
			}
		}
		catch ( Exception $e ) {
			throw new $this->postException( $e->getMessage() );
		} catch (GuzzleException $e) {
            throw new $this->postException( $e->getMessage() );
        }

        return $mediaID;
	}

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getMyInfo () : array
    {
        $this->requestToHomePage();

        try
        {
            $fetchInfo = $this->getApiClient()->get( 'https://x.com/i/api/graphql/hXkPYUuiQAltqmDjG3G9Dw/Viewer', [
                'query'   => [
                    'variables' => json_encode([
                        'withUserResults' => true,
                        'withSuperFollowsUserFields' => true,
                        'withNftAvatar' => false
                    ], JSON_THROW_ON_ERROR)
                ]
            ]);
        }
        catch (Exception $e)
        {
            throw new $this->authException( $e->getMessage() );
        }

        $body = $fetchInfo->getBody()->getContents();

        try {
            $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new $this->authException('Authentication failed!');
        }

        if (isset($body['errors'][0]['message'])) {
            throw new $this->authException($body['errors'][0]['message']);
        }

        if (! isset(
            $body['data']['viewer']['user_results']['result']['rest_id'],
            $body['data']['viewer']['user_results']['result']['legacy']['screen_name'])) {
            throw new $this->authException('Authentication failed!');
        }

        return [
            'id_str'            => $body['data']['viewer']['user_results']['result']['rest_id'] ?? '',
            'screen_name'       => $body['data']['viewer']['user_results']['result']['legacy']['screen_name'] ?? '',
            'name'              => $body['data']['viewer']['user_results']['result']['legacy']['name'] ?? '',
            'profile_image_url' => $body['data']['viewer']['user_results']['result']['legacy']['profile_image_url_https'] ?? ''
        ];
	}

    private function requestToHomePage (): void
    {
        if (empty($this->homePageHtml)) {
            $homePageResponse = $this->getClient()->get('https://x.com');
            $this->homePageHtml = $homePageResponse->getBody()->getContents();

            foreach ($homePageResponse->getHeader('set-cookie') as $setCookie) {
                preg_match( '/ct0=(.*?);/', $setCookie, $cookie );

                if (! empty($cookie[1])) {
                    $this->csrfToken = $cookie[1];
                }
            }

            sleep(random_int(1,3));
        }
    }

	private function generateTransactionId ( string $method, string $path ): string
	{
        $transactionGenerator = new XClientTransactionGenerator();

        preg_match( '/"ondemand\.s":"([a-z0-9]+)"/', $this->homePageHtml, $matches );

        $ondemandHash = $matches[1] ?? null;
        $ondemandUrl = $ondemandHash ? 'https://abs.twimg.com/responsive-web/client-web/ondemand.s.' . $ondemandHash . 'a.js' : null;

        if ( ! empty( $ondemandUrl ) )
        {
            $ondemandFileResponse = $this->getClient()->get($ondemandUrl);
            $ondemandFileContent = $ondemandFileResponse->getBody()->getContents();

            // Parse indices from ondemand file
            $transactionGenerator->getIndices( $ondemandFileContent );
        }

        return $transactionGenerator->generate( $method, $path, $this->homePageHtml );
	}
}
