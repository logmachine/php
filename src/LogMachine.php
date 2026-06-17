<?php
namespace Bufferpunk\Logmachine;

use Bufferpunk\Logmachine\ColorLogger;
use Bufferpunk\Logmachine\Formatters\ColoredLineFormatter;
use Bufferpunk\Logmachine\Formatters\PlainLineFormatter;
use Bufferpunk\Logmachine\Transports\HttpTransport;
use Bufferpunk\Logmachine\Transports\WebSocketTransport;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Level;
use Monolog\LogRecord;

class LogMachine
{
    public static function create(array $config = []): ColorLogger
    {
        $logger = new ColorLogger($config['channel'] ?? 'logmachine');

        // === Terminal (colored) formatter
        $terminalOutput = "%extra.datetime_colored%%extra.level_colored% %message%\n" .
                          "➢ Log provided by: %extra.user%@%extra.module%\n";

        /** this is how all logmachine outputs look */
        $loggerOutput = "(%extra.user% @ %extra.module%) 🤌 CL Timing: %extra.datetime_colored%\n" .
                        "%extra.level_colored% %message%\n 🏁\n";

        if (!empty($config['central']['default_format']) && $config['central']['default_format'] === true) {
            $terminalFormatter = new ColoredLineFormatter(
                $loggerOutput,
                "Y-m-d H:i:s T",
                true,
                true
            );
        } else {
            $terminalFormatter = new ColoredLineFormatter(
                $terminalOutput,
                "Y-m-d H:i:s T",
                true,
                true
            );
        }

        // === File (plain) formatter  – same layout, no ANSI / no emoji
        $plainOutput = "%extra.datetime_colored%%extra.level_colored% %message%\n" .
                       "> Log provided by: %extra.user%@%extra.module%\n";

        $plainFormatter = new PlainLineFormatter(
            $plainOutput,
            "Y-m-d H:i:s T",
            true,
            true
        );

        // === Stream to stdout
        $stdout = new StreamHandler('php://stdout', $config['level'] ?? ColorLogger::DEBUG);
        $stdout->setFormatter($terminalFormatter);
        $logger->pushHandler($stdout);

        // === File logs (plain text)
        if (!empty($config['log_file'])) {
            $fileHandler = new StreamHandler($config['log_file'], ColorLogger::DEBUG);
            $fileHandler->setFormatter($plainFormatter);
            $logger->pushHandler($fileHandler);
        }

        // === Error logs (plain text)
        if (!empty($config['error_file'])) {
            $errorHandler = new StreamHandler($config['error_file'], ColorLogger::ERROR);
            $errorHandler->setFormatter($plainFormatter);
            $logger->pushHandler($errorHandler);
        }

        // === Optional fallback logger for transport errors
        $fallbackLogger = null;
        if (!empty($config['transport_error_file'])) {
            $fallbackLogger = new Logger('transport_errors');
            $fallbackHandler = new StreamHandler($config['transport_error_file'], Logger::ERROR);
            $fallbackHandler->setFormatter(new LineFormatter(null, null, true, true));
            $fallbackLogger->pushHandler($fallbackHandler);
        }

        // === Central HTTP Transport
        if (!empty($config['central']['http_enabled']) && $config['central']['http_enabled'] === true) {

            $centralCfg = $config['central'];

            // Generate user & module
            [$user, $module] = self::resolveUserAndModule($config);

            // If no room name is provided, auto-generate one
            if (empty($centralCfg['room'])) {
                /**
                 * this might currently cause a bug, im asumming user names are unique
                 * if at all there is a future error, just don't be lazy and setup the
                 * room name lol :P
                 */
                $centralCfg['room'] = strtolower($user . '_' . $module);
            }

            // Push HTTP transport handler
            $httpTransport = new HttpTransport(
                fn(string $logText) => self::parseLog($logText, $user, $module),
                $centralCfg,
                $config['level'] ?? ColorLogger::DEBUG,
                true,
                $fallbackLogger,
                (int) ($config['http_retries'] ?? 2),
                (int) ($config['http_retry_delay'] ?? 1)
            );

            $logger->pushHandler($httpTransport);
        }

        // === WebSocket Transport
        if (!empty($config['central']['websocket_enabled']) && $config['central']['websocket_enabled'] === true) {

            [$user, $module] = self::resolveUserAndModule($config);

            $wsTransport = new WebSocketTransport(
                fn(string $logText) => self::parseLog($logText, $user, $module),
                $config['central'],
                $config['level'] ?? ColorLogger::DEBUG,
                true,
                $fallbackLogger
            );

            $logger->pushHandler($wsTransport);
        }

        // === Context enrichment
        $logger->pushProcessor([self::class, 'enrichContext']);

        return $logger;
    }

    public static function enrichContext(LogRecord $record): LogRecord
    {
        $record = $record->with(
            context: $record->context,
            extra: array_merge($record->extra, [
                'user'   => getenv('CL_USERNAME') ?: get_current_user(),
                'module' => basename(getcwd())
            ])
        );

        return $record;
    }

    private static function resolveUserAndModule(array $config): array
    {
        $user   = $config['central']['user']   ?? null;
        $module = $config['central']['module'] ?? null;

        if ($user && $module) {
            return [$user, $module];
        }

        // Create temporary record to enrich with defaults
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'logmachine',
            level: Level::Info,
            message: '',
            context: [],
            extra: [],
            formatted: null
        );

        $enriched = self::enrichContext($record);

        return [
            $user   ?? $enriched->extra['user'],
            $module ?? $enriched->extra['module'],
        ];
    }

    public static function parseLog(string $logText, string $defaultUser, string $defaultModule): ?array
    {
        $logText = trim($logText);
        $logText = preg_replace('/\x1b\[[0-9;]*m/', '', $logText); // remove ANSI codes
        $logText = str_replace("\xf0\x9f\x8f\x81", '', $logText);   // remove 🏁 emoji

        // Attempt to parse structured log
        if (!preg_match('/\((.*?) @ (.*?)\) ⧗ CL Timing: \[ (.*?) \]/', $logText, $matches)) {
            return [
                'user'      => $defaultUser,
                'module'    => $defaultModule,
                'level'     => 'INFO',
                'timestamp' => date(\DateTimeInterface::RFC3339),
                'message'   => $logText
            ];
        }

        [$full, $user, $module, $timestamp] = $matches;
        $lines = explode("\n", $logText);
        $levelLine = $lines[1] ?? '';

        if (!preg_match('/\[\s?(\w+)\s?\]\s?(.*)/', $levelLine, $levelMatches)) {
            return [
                'user'      => $user,
                'module'    => $module,
                'level'     => 'INFO',
                'timestamp' => $timestamp,
                'message'   => $logText
            ];
        }

        [$_, $level, $message] = $levelMatches;

        return [
            "user"      => $user,
            "module"    => $module,
            "level"     => trim($level),
            "timestamp" => $timestamp,
            "message"   => trim($message)
        ];
    }
}
