<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp HTTP API (WAHA) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WAHA (WhatsApp HTTP API) integration
    |
    */

    'waha_url' => env('WAHA_URL', 'http://localhost:3000'),
    'session_id' => env('WAHA_SESSION_ID', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Default Country Code
    |--------------------------------------------------------------------------
    |
    | Default country code for phone number formatting
    | Indonesia: 62
    |
    */
    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '62'),

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for WhatsApp messages
    |
    */
    'timeout' => env('WHATSAPP_TIMEOUT', 30),
    'retry_attempts' => env('WHATSAPP_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('WHATSAPP_RETRY_DELAY', 5), // seconds
];
