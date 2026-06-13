<?php

use Laravel\Fortify\Features;

return [

    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'P_CODE',

    'email' => 'P_EMAIL',

    'lowercase_usernames' => false,

    'home' => '/dashboard',

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web'],

    'limiters' => [
        'login' => null,       // We handle our own throttling via password_failure.
        'two-factor' => 'two-factor',
    ],

    'views' => false,          // We provide our own views; disable Fortify's route/view pairs.

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    | Only twoFactorAuthentication is enabled. All other Fortify features are
    | disabled — authentication, registration, profile updates etc. are handled
    | by the app's custom auth layer.
    */

    'features' => [
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]),
    ],

];
