<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flutterwave Public Key
    |--------------------------------------------------------------------------
    |
    | Your Flutterwave public key from your dashboard.
    | For test mode, use your test public key.
    |
    */
    'public_key' => env('FLW_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Secret Key
    |--------------------------------------------------------------------------
    |
    | Your Flutterwave secret key from your dashboard.
    | For test mode, use your test secret key.
    |
    */
    'secret_key' => env('FLW_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Secret Hash
    |--------------------------------------------------------------------------
    |
    | Your Flutterwave secret hash for webhook verification.
    | Get this from your Flutterwave dashboard under Settings > API Keys.
    |
    */
    'secret_hash' => env('FLW_SECRET_HASH', ''),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'test' for sandbox/test mode or 'live' for production.
    |
    */
    'environment' => env('FLW_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Flutterwave API.
    | Automatically set based on environment.
    |
    */
    'base_url' => env('FLW_BASE_URL', env('FLW_ENVIRONMENT', 'test') === 'test' 
        ? 'https://api.flutterwave.com/v3' 
        : 'https://api.flutterwave.com/v3'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency code (NGN for Nigerian Naira).
    |
    */
    'currency' => env('FLW_CURRENCY', 'NGN'),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods to show to users.
    |
    */
    'payment_methods' => [
        'card',
        'bank_transfer',
        'mobile_money',
    ],
];
