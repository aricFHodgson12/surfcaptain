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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],

    'subscription' => [
        'trial_days' => 14
    ],

    'campaign_monitor' => [
        'campaign_monitor_api_key' => env('CAMPAIGN_MONITOR_API_KEY', 'Hs00IX/8LNdNM4nGnnqeovKSN4/p6oKdzZogqsu3vlSE+InSZv2hEZ8LQUsu9Zu5etmZqeHnez7XM4dBO5eLybPE6Q+2Q1TzB+kIWgkkG4vZJceY/oTgHbJm7QO2sUah955DMD1Id8ssYMveLkN07Q=='),
        'campaign_monitor_client_id' => env('CAMPAIGN_MONITOR_CLIENT_ID', '959b13b6022556eb1408c53e536ff00e'),
        'campaign_monitor_subscriber_list_id' => env('CAMPAIGN_MONITOR_SUBSCRIBERS_LIST_ID', '5f6f62cbd3a75360557517986494c95f')
    ],

    'stripe' => [
        'stripe_key' => env('STRIPE_KEY'),
        'stripe_secret' => env('STRIPE_SECRET'),
        'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET')
    ]

];
