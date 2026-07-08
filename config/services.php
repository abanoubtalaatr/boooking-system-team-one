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

    'sms_masr' => [
        'endpoint' => env('SMS_MASR_ENDPOINT', 'https://smsmisr.com/api/SMS/'),
        'username' => env('SMS_MASR_USERNAME'),
        'password' => env('SMS_MASR_PASSWORD'),
        'sender' => env('SMS_MASR_SENDER'),
        'language' => env('SMS_MASR_LANGUAGE', '1'),
        'environment' => env('SMS_MASR_ENVIRONMENT', '1'),
    ],

];
