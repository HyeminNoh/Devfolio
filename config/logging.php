<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        // 다중 채널을 생성할 수 있는 채널
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'syslog'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'tap' => [App\Logging\CustomizeFormatter::class],
            'path' => storage_path('logs/info/'.date('Y-m-d').'.log'),
            'level' => 'info',
        ],

        'syslog' => [
            'driver' => 'syslog',
            'tap' => [App\Logging\CustomizeFormatter::class],
            'path' => storage_path('logs/debug/'.date('Y-m-d').'.log'),
            'level' => 'debug',
        ],
    ],

];
