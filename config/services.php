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
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'payment' => [
        'default' => 'vnpay',

        'vnpay' => [
            'tmn_code'      => env('VNPAY_TMN_CODE', 'YOUR_TMN_CODE'),
            'hash_secret'   => env('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET'),
            'url'           => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
            'api_url'       => env('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),
            'return_url'    => env('VNPAY_RETURN_URL', 'http://localhost/payment/vnpay/return'),
            'refund_mock'   => env('VNPAY_REFUND_MOCK', false),
            'withdraw_mock' => env('VNPAY_WITHDRAW_MOCK', false),
            'version'       => env('VNPAY_API_VERSION', ''),
        ],
    ],

    'ai' => [
        'provider'                => env('AI_PROVIDER', 'openai'),
        'base_url'                => env('AI_BASE_URL', 'https://api.openai.com/v1'),
        'api_key'                 => env('AI_API_KEY'),
        'model'                   => env('AI_MODEL', 'gpt-4o-mini'),
        'timeout'                 => (int) env('AI_TIMEOUT', 30),
        'temperature'             => (float) env('AI_TEMPERATURE', 0.3),
        'chat_history_limit'      => (int) env('AI_CHAT_HISTORY_LIMIT', 10),
        'dashboard_cache_seconds' => (int) env('AI_DASHBOARD_CACHE_SECONDS', 600),
        'reasoning_enabled'       => filter_var(env('AI_REASONING_ENABLED', true), FILTER_VALIDATE_BOOL),
        'http_referer'            => env('AI_HTTP_REFERER', env('APP_URL')),
        'app_name'                => env('AI_APP_NAME', env('APP_NAME', 'KeyCove')),
    ],

];
