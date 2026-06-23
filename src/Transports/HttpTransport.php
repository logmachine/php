<?php

namespace Bufferpunk\Logmachine\Transports;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

class HttpTransport extends AbstractProcessingHandler
{
    private Client $client;
    private array $config;
    private $logParser;
    private ?LoggerInterface $fallbackLogger;
    private int $retryAttempts;
    private int $retryDelay;
    private bool $enabled;

    /**
     * @param callable $logParser Function that parses log string into array: function(string $formatted): ?array
     * @param array $central Logging configuration (must include 'url' and 'room' unless disabled)
     * @param int|string $level Minimum logging level
     * @param bool $bubble Whether to bubble logs up the stack
     * @param LoggerInterface|null $fallbackLogger PSR-3 logger for transport errors
     * @param int $retryAttempts How many times to retry on failure
     * @param int $retryDelay Delay in seconds between retries (exponential)
     */
    public function __construct(
        callable $logParser,
        array $central,
        int|string $level = Logger::DEBUG,
        bool $bubble = true,
        ?LoggerInterface $fallbackLogger = null,
        int $retryAttempts = 2,
        int $retryDelay = 1
    ) {
        parent::__construct($level, $bubble);

        $this->enabled = $central['http_enabled'] ?? false;
        $this->logParser = $logParser;
        $this->fallbackLogger = $fallbackLogger;
        $this->retryAttempts = max(0, $retryAttempts);
        $this->retryDelay = max(1, $retryDelay);

        if ($this->enabled) {
            $this->validateConfig($central);
            $this->config = $this->normalizeConfig($central);
            $this->initializeHttpClient();
        }
    }

    private function validateConfig(array $central): void
    {
        if (empty($central['room'])) {
            // console log ONLY when HTTP enabled and no room set
            echo "[LogMachine] Warning: HTTP transport is enabled but no room was provided.\n";
        }

        if (empty($central['url'])) {
            throw new \InvalidArgumentException("Missing required config key: 'url'");
        }

        if (!filter_var($central['url'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL in 'central.url'");
        }
    }

    private function normalizeConfig(array $central): array
    {
        return array_merge([
            'endpoint' => '/api/logs',
            'headers' => [],
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify_ssl' => true,
            'user_agent' => 'LogMachine-HttpTransport/1.0',
            'room' => $central['room'] ?? 'default-room',
        ], $central);
    }

    private function initializeHttpClient(): void
    {
        $this->client = new Client([
            'timeout' => $this->config['timeout'],
            'connect_timeout' => $this->config['connect_timeout'],
            'verify' => $this->config['verify_ssl'],
            'headers' => [
                'User-Agent' => $this->config['user_agent'],
            ],
        ]);
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->enabled) {
            return;
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->retryAttempts) {
            try {
                $this->sendLogData($record);
                return; // Success
            } catch (ConnectException | RequestException $e) {
                $shouldRetry = $e instanceof ConnectException || $this->isRetryableHttpError(
                    $e->getResponse()?->getStatusCode()
                );

                if (!$shouldRetry) {
                    $this->logTransportError($e, $record, false);
                    return;
                }

                $lastException = $e;
                $this->handleRetry($attempt, $e);
            } catch (\Exception $e) {
                $this->logTransportError($e, $record, false);
                return;
            }

            $attempt++;
        }

        if ($lastException) {
            $this->logTransportError($lastException, $record, true);
        }
    }

    private function sendLogData(LogRecord $record): void
    {
        $logData = ($this->logParser)($record->message);

        if (!is_array($logData)) {
            throw new \RuntimeException('Parsed log must return an array.');
        }

        $url = rtrim($this->config['url'], '/') . '/' . ltrim($this->config['endpoint'], '/')
             . '?' . http_build_query(['room' => $this->config['room']]);

        $headers = array_merge(
            ['Content-Type' => 'application/json'],
            $this->config['headers']
        );

        $token = getenv('LM_AUTH_TOKEN') ?: getenv('lm_auth_token') ?: ($this->config['auth'] ?? null);
        if ($token !== null && $token !== '') {
            $hasAuth = false;
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $hasAuth = true;
                    break;
                }
            }
            if (!$hasAuth) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
        }

        $enriched = [
            'user'      => $logData['user'] ?? '',
            'module'    => $logData['module'] ?? '',
            'level'     => $record->level->getName(),
            'timestamp' => $record->datetime->format(\DateTimeInterface::RFC3339_EXTENDED),
            'message'   => $logData['message'] ?? $record->message,
        ];

        $this->client->post($url, [
            'headers' => $headers,
            'json' => $enriched,
        ]);
    }

    private function handleRetry(int $attempt, \Exception $e): void
    {
        if ($attempt < $this->retryAttempts) {
            $delay = $this->retryDelay * (2 ** $attempt);

            $this->fallbackLogger?->warning(
                "Retrying log send ({attempt}/{total}) in {delay}s: {reason}",
                [
                    'attempt' => $attempt + 1,
                    'total' => $this->retryAttempts,
                    'delay' => $delay,
                    'reason' => $e->getMessage(),
                ]
            );

            sleep($delay);
        }
    }

    private function isRetryableHttpError(?int $code): bool
    {
        return in_array($code, [429, 500, 502, 503, 504], true);
    }

    private function logTransportError(\Exception $e, LogRecord $record, bool $retriesExhausted): void
    {
        $context = [
            'exception' => $e->getMessage(),
            'exception_class' => get_class($e),
            'original_level' => $record->level->getName(),
            'original_channel' => $record->channel,
            'retries_exhausted' => $retriesExhausted,
        ];

        if ($e instanceof RequestException && $e->hasResponse()) {
            $context['http_status'] = $e->getResponse()->getStatusCode();
            $context['http_body'] = (string)$e->getResponse()->getBody();
        }

        if ($this->fallbackLogger) {
            $this->fallbackLogger->error("HTTP log transport failed: {exception}", $context);
        } else {
            error_log("LogMachine HTTP Transport Error: {$e->getMessage()} | Context: " . json_encode($context));
        }
    }

    public function getConfig(): array
    {
        $config = $this->config ?? [];
        if (isset($config['headers']['Authorization'])) {
            $config['headers']['Authorization'] = '[REDACTED]';
        }
        return $config;
    }

    public function testConnection(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $url = rtrim($this->config['url'], '/') . '/health';
            $response = $this->client->get($url, ['timeout' => 5]);
            return $response->getStatusCode() < 400;
        } catch (\Exception $e) {
            throw new \RuntimeException("Connection test failed: " . $e->getMessage(), 0, $e);
        }
    }
}
