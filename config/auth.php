<?php

return [

    'defaults' => [
    'guard' => 'web', // ✅ WAJIB WEB
    'passwords' => 'users',
],


    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // ✅ GUARD UNTUK API TOUR LEADER
        'tourleader' => [
            'driver' => 'sanctum',
            'provider' => 'tourleaders',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'tourleaders' => [
            'driver' => 'eloquent',
            'model' => App\Models\TourLeader::class,
        ],
    ],
];
