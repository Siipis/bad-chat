<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Chat security
    |--------------------------------------------------------------------------
    |
    | Controls the level of security used to verify sessions
    |
    */

    "verify" => [
        "agent" => true,
        "ip" => true,
        "session" => true,
    ],

    "allow" => [
        "ipChange" => true,
    ]
];