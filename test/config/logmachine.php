<?php

return [

    'log_file' => __DIR__ . '/logs/all_logs.log',
    'error_file' => __DIR__ . '/logs/error_logs.log',
    'transport_error_file' => __DIR__ . '/logs/transport-error.log',

    /*
    |--------------------------------------------------------------------------
    | Local Logger Settings
    |--------------------------------------------------------------------------
    | Control how logs are displayed locally in your app (console/file).
    | - "color" => true enables colored log output
    | - "emoji" => true adds emojis per log level
    */
    'local' => [
        'color' => true,
        'emoji' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Central Logging Settings
    |--------------------------------------------------------------------------
    | Configure remote log forwarding.
    |
    | - "http_enabled": Set to true to enable HTTP log transport
    | - "url": Central logging server URL (required if http_enabled is true)
    | - "room": Logical room/channel for your logs.
    |   If omitted, LogMachine will auto-generate a unique name.
    | - "user": You can uncomment this section and add your user name
    | - "module": You can edit this to the name of the project yo working on,
    |   The name or your org or anything meaningful.
    | - "headers": Optional extra headers (e.g. auth token)
    | - "timeout" / "connect_timeout": Network timeouts in seconds
    | - "verify_ssl": Whether to validate SSL certificates
    */
    'central' => [
        'http_enabled'    => false,    // can edit: disable by default
        'url'             => 'https://logmachine.bufferpunk.com', // default link
        'room'            => null,    // can edit: I preffer you just add a name
        //'user' => 'Test-user', // can edit: change to your user name (optional but a plus)
        //'module' => 'logmachine-php', // can edit: your module (optional but a plus)
        'headers'         => [
            // 'Authorization' => 'Bearer YOUR_TOKEN_HERE',
        ],
        'timeout'         => 30,
        'connect_timeout' => 10,
        'verify_ssl'      => true,
        'user_agent'      => 'LogMachine-Client/1.0',
    ],

];
