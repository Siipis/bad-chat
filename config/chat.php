<?php
return [
    'login' => [
        'url' => env('APP_URL'),
        'timeout' => 3, // minutes
    ],

    'names' => [
        'system' => 'BadBot',
        'info' => 'Info',
        'error' => 'Error',
        'topic' => 'Topic',
    ],

    'channels' => [
        'backtrack' => 60 * 3, // minutes to backtrack when joining a channel
        'access' => 'private', // default access
        'expire' => 7, // days
    ],

    'ban' => [
        'units' => ['hours', 'days', 'years'],

        'default' => [
            'duration' => 7,
            'unit' => 'hours',
        ],
    ],

    'interval' => [
        'minimum' => 5, // seconds
        'messages' => 5, // seconds
        'notifications' => 600, // 10 minutes
    ],

    'input' => [
        'maxLength' => 1200,
    ],
];