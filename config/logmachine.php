<?php
/**
 * Here you are, the King:
 * But without rules, we are like animals.
 * You can configure this as much as you like
 *
 */
return [
    'channel' => 'logmachine',
    'level' => \Bufferpunk\Logmachine\ColorLogger::DEBUG,
    'log_file' => __DIR__ . '/../logs/logmachine.log',
    'error_file' => __DIR__ . '/../logs/logmachine-error.log',
    'transport_error_file' => __DIR__ . '/../logs/transport-error.log',

    'central' => [
        /** The URL is the Law **/
        'url' => 'https://logmachine.bufferpunk.com', /** DONT EVEN THINK OF TOUCHING ME :P **/
        /** The following is the default format for all logmachine 
         *  If you want a diferent format for your logs, you can set it to false.
        **/
        'default_format' => true,

        /** HTTP transport **/
        'http_enabled' => false, // set to true to send logs via HTTP POST

        /** WebSocket / Socket.IO transport **/
        'websocket_enabled' => false, // set to true to stream logs over Socket.IO
        'socketio_path' => '/api/socket.io/', // Socket.IO server path (default)

        /* The following will be your room id
         * you can change it to what you wish
         * if not provided the Logmachine will auto generate one for ya
         */
        'room' => '',
        /** Uncomment the following if you need to use them my King **/
        //'user' => 'Test-user', // change this to your user name (optional but a plus)
        //'module' => 'logmachine-php', // your module or what yo working on (optional but a plus)
        'auth' => 'your-optional-token', // never mind about em :D
        'headers' => [], // extra headers for the WebSocket handshake / HTTP requests
    ],

    'http_retries' => 2,
    'http_retry_delay' => 1,
];

