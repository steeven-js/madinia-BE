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

    'app' => [
        'frontend_url' => env('APP_URL_FRONT'),
    ],

    'intercom' => [
        'secret_key' => env('INTERCOM_SECRET_KEY'),
    ],

    'stripe' => [
        'live' => [
            'secret' => env('STRIPE_SECRET'),
            'public' => env('STRIPE_PUBLIC'),
            'webhook' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'test' => [
            'secret' => env('STRIPE_TEST_SECRET'),
            'public' => env('STRIPE_TEST_KEY'),
            'webhook' => env('STRIPE_TEST_WEBHOOK_SECRET'),
        ],
    ],

    'bearer' => env('BEARER_TOKEN'),

    'madinia_url' => env('MADINIA_URL'),

    'mail' => [
        'admin_address' => env('MAIL_ADMIN_ADDRESS'),
    ],

];
