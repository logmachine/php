<?php

namespace Bufferpunk\Logmachine\Transports;

use ElephantIO\Client as ElephantClient;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

/**
 * Sends log records to a Socket.IO server via a persistent WebSocket connection.
 */
class WebSocketTransport extends AbstractProcessingHandler
{
    private ?ElephantClient $client = null;
    private array $config;
    private $logParser;
    private ?LoggerInterface $fallbackLogger;
    private bool $enabled;
    private bool $connected = false;

    /**
     * @param callable          $logParser      Parses a formatted log string into an array.
     * @param array             $central        Transport configuration (see class docblock).
     * @param int|string        $level          Minimum log level to handle.
     * @param bool              $bubble         Whether to bubble to other handlers.
     * @param LoggerInterface|null $fallbackLogger PSR-3 logger for transport errors.
     */
    public function __construct(
        callable $logParser,
        array $central,
        int|string $level = Logger::DEBUG,
        bool $bubble = true,
        ?LoggerInterface $fallbackLogger = null
    ) {
        parent::__construct($level, $bubble);

        $this->enabled       = !empty($central['websocket_enabled']);
        $this->logParser     = $logParser;
        $this->config        = $this->normalizeConfig($central);
        $this->fallbackLogger = $fallbackLogger;

        if ($this->enabled) {
            $this->connect();
        }
    }

    private function normalizeConfig(array $central): array
    {
        return array_merge([
            'socketio_path' => '/api/socket.io/',
            'headers'       => [],
            'room'          => 'default-room',
        ], $central);
    }

    /**
     * Open the Socket.IO connection.
     * Errors are caught so that a connection failure does not crash the application.
     */
    private function connect(): void
    {
        try {
            if (empty($this->config['url'])) {
                throw new \InvalidArgumentException("WebSocket transport requires 'central.url'.");
            }
            if (empty($this->config['room'])) {
                throw new \InvalidArgumentException("WebSocket transport requires 'central.room'.");
            }

            $this->client = ElephantClient::create($this->config['url'], [
                'path'    => $this->config['socketio_path'],
                'headers' => $this->config['headers'],
            ]);

            $this->client->connect();
            $this->connected = true;
        } catch (\Throwable $e) {
            $this->connected = false;
            $this->handleTransportError($e, 'WebSocket connect failed');
        }
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->enabled || !$this->connected || $this->client === null) {
            return;
        }

        try {
            $logData = ($this->logParser)($record->formatted ?? $record->message);

            if (!is_array($logData)) {
                throw new \RuntimeException('Log parser must return an array.');
            }

            $this->client->emit('log', [
                'room' => $this->config['room'],
                'data' => $logData,
            ]);
        } catch (\Throwable $e) {
            $this->connected = false;
            $this->handleTransportError($e, 'WebSocket emit failed');
        }
    }

    private function handleTransportError(\Throwable $e, string $context): void
    {
        $message = "LogMachine WebSocketTransport – {$context}: {$e->getMessage()}";

        if ($this->fallbackLogger) {
            $this->fallbackLogger->error($message, ['exception' => $e->getMessage()]);
        } else {
            error_log($message);
        }
    }

    /**
     * Gracefully close the Socket.IO connection when the handler is destroyed.
     */
    public function close(): void
    {
        if ($this->connected && $this->client !== null) {
            try {
                $this->client->disconnect();
            } catch (\Throwable) {
                // best-effort
            }
            $this->connected = false;
        }

        parent::close();
    }

    public function __destruct()
    {
        $this->close();
    }
}

