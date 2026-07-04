<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | The provider-agnostic SMS layer (see App\Services\Sms) resolves one of the
    | drivers below. "log" writes to the log (dev-safe, sends nothing), "null"
    | silently discards, and "smsgatewayme" talks to the SMSGateway.me device
    | API. See docs/admin/sms.md for setup.
    |
    | Supported: "log", "null", "smsgatewayme"
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender
    |--------------------------------------------------------------------------
    |
    | The sender id / originator shown to recipients, where the provider allows
    | it. Alphanumeric sender ids are not supported by every gateway or country.
    |
    */

    'from' => env('SMS_FROM', 'OpenBrigade'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    */

    'drivers' => [

        'smsgatewayme' => [
            'token' => env('SMSGATEWAYME_TOKEN'),
            'device_id' => env('SMSGATEWAYME_DEVICE_ID'),
            'endpoint' => env('SMSGATEWAYME_ENDPOINT', 'https://smsgateway.me/api/v4'),
        ],

    ],

];
