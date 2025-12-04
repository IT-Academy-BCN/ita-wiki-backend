<?php
$isProd = env('APP_ENV') === 'production';
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
    
    'github' => [
    'client_id' => env($isProd ? 'GITHUB_CLIENT_ID_PROD' : 'GITHUB_CLIENT_ID_LOCAL'),
    'client_secret' => env($isProd ? 'GITHUB_CLIENT_SECRET_PROD' : 'GITHUB_CLIENT_SECRET_LOCAL'),
    'redirect' => env($isProd ? 'GITHUB_REDIRECT_URI_PROD' : 'GITHUB_REDIRECT_URI_LOCAL'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
