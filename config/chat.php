<?php
return [
    'configPath' => storage_path('app/config.json'),

    'login' => [
        'url' => env('APP_URL'),
        'timeout' => env('SESSION_TIMEOUT', 3), // minutes
    ],

    'names' => [
        'system' => 'BadBot',
        'info' => 'Info',
        'error' => 'Error',
        'topic' => 'Topic',
    ],

    'channels' => [
        'backtrack' => 20, // messages to backtrack when joining a channel
        'access' => 'private', // default access
        'expire' => 7, // days
        'minlength' => 3, // characters
        'maxlength' => 16, // characters

        'maxslow' => 60, // seconds
    ],

    'ban' => [
        'units' => ['hours', 'days', 'years'],

        'default' => [
            'duration' => 7,
            'unit' => 'hours',
        ],
    ],

    'discourage' => 150, // once every ... request

    'interval' => [
        'minimum' => 5, // seconds
        'messages' => 5, // seconds
        'notifications' => 300, // 5 minutes
    ],

    'input' => [
        'maxLength' => 1200,
    ],
];