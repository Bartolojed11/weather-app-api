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

    'location' => [
        'url' =>  env('GEO_API_URL'),
        'key' =>  env('GEO_API_KEY'),
        'version' => env('GEO_API_VERSION'),
    ],
    'weather' => [
        'url' =>  env('OPEN_WEATHER_API_URL'),
        'key' =>  env('OPEN_WEATHER_KEY'),
        'version' => env('OPEN_WEATHER_VERSION'),
        'icon_url' => env('OPEN_WEATHER_ICON_URL')
    ]
];
