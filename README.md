# LogMachine PHP

A powerful structured logging package for PHP applications.
It provides colored console output, structured log formatting, and optional log forwarding via HTTP or WebSockets to a remote LogMachine server.

## Why LogMachine?

If you’ve used plain `error_log()` or even Monolog, you know logs can quickly become hard to scan, messy to parse, and painful to centralize.
LogMachine solves this with:

| Feature                          | Why it matters                                       |
| -------------------------------- | ---------------------------------------------------- |
| **Color-coded CLI logs**         | Spot warnings and errors instantly in your terminal. |
| **Structured JSON output**       | Easy to pipe into ELK, Loki, or any log processor.   |
| **Remote forwarding**            | Ship logs to your log server without extra setup.    |
| **Automatic context enrichment** | No more boilerplate for `user` and `module`.         |
| **PSR-friendly**                 | Works standalone or integrates with Monolog.         |
| **Small footprint**              | No heavy dependencies — drop it in and go.           |

### CLI Output Example (colored):

```css
[2025-08-08 14:32:00 UTC] [DEBUG] Debug message
➢ Log provided by: username@room
[2025-08-08 14:32:01 UTC] [INFO]  User logged in  {"user":"jdoe"}
➢ Log provided by: username@room
[2025-08-08 14:32:02 UTC] [WARNING] API rate limit approaching
➢ Log provided by: username@room
[2025-08-08 14:32:03 UTC] [ERROR]  	Database connection failed  {"host":"localhost"}
➢ Log provided by: username@room
```

## Compatibility

This package is compatible with PHP v8.3.6 and above

## 📦 Installation

Install via Composer:

```bash
composer require bufferpunk/logmachine

or

composer require bufferpunk/logmachine dev-main

or

composer require bufferpunk/logmachine dev-main --ignore-platform-req=ext-mbstring
```

<br>

<details>
<summary>
: Additional steps (Important)
</summary>
<br>

- create config directory 

```bash
mkdir config
```

- create a config file

```bash
touch config/logmachine.php
```

- copy paste this to your logmachine.php file, then save:

```php
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
```

</details>


## 🚀 Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Bufferpunk\Logmachine\LogMachine;

// Load config (example: config/logmachine.php)
$config = require __DIR__ . '/config/logmachine.php';

// Create logger instance
$logger = LogMachine::create($config);

// Log messages
$logger->debug('Debug message');
$logger->info('User logged in', ['user' => 'jdoe']);
$logger->warning('API rate limit approaching');
$logger->error('Database connection failed', ['host' => 'localhost']);
$logger->success('Operation completed successfully!');

```

## Configuration

You can pass the following configuration array when creating the Logger. `LogMachine::create($config)`:

| Option                 | Type     | Default      | Description                                            |
| ---------------------- | -------- | ------------ | ------------------------------------------------------ |
| `channel`              | `string` | `logmachine` | Logger channel name                                    |
| `level`                | `int`    | `DEBUG`      | Minimum log level to output (Monolog levels supported) |
| `log_file`             | `string` | `null`       | Path to general log file                               |
| `error_file`           | `string` | `null`       | Path to error-only log file                            |
| `transport_error_file` | `string` | `null`       | Path for transport error logs (e.g., HTTP failures)    |
| `http_retries`         | `int`    | `2`          | Retries for failed HTTP requests                       |
| `http_retry_delay`     | `int`    | `1` second   | Delay between HTTP retries                             |
| `central`              | `array`  | `null`       | Remote log forwarding config (see below)               |
| `enable_websocket`     | `bool`   | `false`      | Enable WebSocket transport (not yet implemented)       |


## Log Levels

LogMachine supports these log levels

| Level       | Description                       |
| ----------- | --------------------------------- |
| `Debug`     | Detailed debug info               |
| `Info`      | General application events        |
| `Notice`    | Normal but significant conditions |
| `Warning`   | Warnings that require attention   |
| `Error`     | Runtime errors                    |
| `Critical`  | Critical conditions               |
| `Alert`     | Immediate action required         |
| `Emergency` | System unusable                   |

- Additionally, ColorLogger provides extra methods like:

	*`$logger->success("Task completed!");`*



## 📡 Remote Forwarding (Central Logging)

To forward logs to a central log server, configure the central key:

```php
'central' => [
    'room'   => 'my_app_room',   // optional, see note below
    'user'   => 'rezzcode',      // optional
    'module' => 'backend_api',   // optional
],
```

> [!NOTE]<br>
> If no transport is provided, logs are written locally only.

### Room Naming Rule

If room is not provided, LogMachine will automatically generate one:

```bash
<user>_<module><random_number>
```
> Example: rezzcode_backend23

- This ensures every client has a unique room name, even if they skip configuration.

## stracture

```
logmachine-php/
├── LICENSE
├── README.md
├── compos_install.sh
├── composer.json
├── src
│   ├── ColorLogger.php
│   ├── Formatters
│   │   ├── ColoredLineFormatter.php
│   │   └── PlainLineFormatter.php
│   ├── LogMachine.php
│   ├── Traits
│   │   └── ColorfulLoggerTrait.php
│   └── Transports
│       ├── HttpTransport.php
│       └── WebSocketTransport.php
├── public/
│   └── index.php (demo/test endpoint)
├── config/
│   └── logmachine.php
└── test
```
