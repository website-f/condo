<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'shared_assets' => [
        'listing_base_url' => env('SHARED_LISTING_IMAGE_BASE_URL', 'https://ipushproperty.com/my/cloudstorage/cobroke'),
        'profile_base_url' => env('SHARED_PROFILE_IMAGE_BASE_URL', 'https://ipushproperty.com/my/IPP/users/photos'),
        'wordpress_site_url' => env('WORDPRESS_SITE_URL', 'https://condo.com.my'),
        'wordpress_media_base_url' => env('WORDPRESS_MEDIA_BASE_URL', 'https://condo.test'),
        'bridge_sync_secret' => env('CONDO_LARAVEL_BRIDGE_SYNC_SECRET'),
        'public_base_host' => env(
            'CONDO_PUBLIC_BASE_HOST',
            parse_url((string) env('WORDPRESS_SITE_URL', 'https://condo.com.my'), PHP_URL_HOST) ?: 'condo.com.my'
        ),
        'public_reserved_subdomains' => array_values(array_filter(array_map(
            static fn (string $value) => strtolower(trim($value)),
            explode(',', (string) env(
                'CONDO_PUBLIC_RESERVED_SUBDOMAINS',
                'www,admin,agent,api,mail,ftp,cpanel,webmail,autodiscover,cpcontacts,cpcalendars'
            ))
        ))),
    ],

];
