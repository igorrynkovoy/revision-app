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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'socket' => [
        'domain' => env('SOCKET_DOMAIN')
    ],

    'remote-revision' => [
        'host' => env('REMOTE_REVISION_API_HOST'),
        'api' => [
            'key' => env('REMOTE_REVISION_API_KEY')
        ]
    ],

    'etherscan' => [
        'key' => env('ETHERSCAN_KEY')
    ],

    'blockchair' => [
        'key' => env('BLOCKCHAIR_KEY')
    ],

    'cryptoapis' => [
        'key' => env('CRYPTOAPIS_KEY')
    ],

    'litecoin-wallet' => [
        'host' => env('LITECOIN_HOST'),
    ],

];
