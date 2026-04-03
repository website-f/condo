<?php

return [

    'defaults' => [
        'guard' => 'agent',
        'passwords' => 'agents',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'agent' => [
            'driver' => 'session',
            'provider' => 'agents',
        ],
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'agents',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'agents' => [
            'driver' => 'eloquent',
            'model' => App\Models\Agent::class,
        ],
    ],

    'passwords' => [
        'agents' => [
            'provider' => 'agents',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
