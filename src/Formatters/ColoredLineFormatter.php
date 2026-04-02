<?php

namespace Bufferpunk\Logmachine\Formatters;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class ColoredLineFormatter extends LineFormatter
{
    protected array $levelColors = [
        'DEBUG'     => "\033[1;30m [ DEBUG ] 🐞\033[0m",
        'INFO'      => "\033[1;34m [ INFO ] ℹ️\033[0m",
        'NOTICE'    => "\033[1;36m [ NOTICE ] 🔰\033[0m",
        'WARNING'   => "\033[1;33m [ WARNING ] ⚠️\033[0m",
        'ERROR'     => "\033[1;31m [ ERROR ] ‼️\033[0m",
        'CRITICAL'  => "\033[1;35m [ CRITICAL ] 🔥\033[0m",
        'ALERT'     => "\033[1;41m [ ALERT ] 🚨\033[0m",
        'EMERGENCY' => "\033[1;41m [ EMERGENCY ] 🛑\033[0m",
    ];
    protected array $messageColors = [
        'DEBUG'     => "\033[1;30m",
        'INFO'      => "\033[1;34m",
        'NOTICE'    => "\033[1;36m",
        'WARNING'   => "\033[1;33m",
        'ERROR'     => "\033[1;31m",
        'CRITICAL'  => "\033[1;35m",
        'ALERT'     => "\033[1;41m",
        'EMERGENCY' => "\033[1;41m",
    ];
    protected string $resetColor = "\033[0m";

    public function format(array|LogRecord $record): string
    {
        if ($record instanceof LogRecord) {
            $levelName    = $record->level->getName();
            $isSuccess    = isset($record->context['_success']) && $record->context['_success'];
            $levelColored = $this->buildLevelColored($levelName, $isSuccess);
            $coloredDatetime = $this->buildColoredDatetime(
                $record->datetime,
                $levelName,
                $isSuccess
            );

            $record = $record->with(
                message: $this->colorMessage($record->message, $levelName),
                extra: array_merge($record->extra, [
                    'level_colored'    => $levelColored,
                    'datetime_colored' => $coloredDatetime,
                ])
            );
        } else {
            // Back-compat with Monolog v2 array records
            $levelName    = $record['level_name'];
            $isSuccess    = isset($record['context']['_success']) && $record['context']['_success'];
            $levelColored = $this->buildLevelColored($levelName, $isSuccess);
            $coloredDatetime = $this->buildColoredDatetime(
                $record['datetime'],
                $levelName,
                $isSuccess
            );

            $record['message']                    = $this->colorMessage($record['message'], $levelName);
            $record['extra']['level_colored']    = $levelColored;
            $record['extra']['datetime_colored'] = $coloredDatetime;
        }

        return parent::format($record);
    }

    /**
     * Build the colored level label, handling the special success-INFO variant.
     */
    protected function buildLevelColored(string $levelName, bool $isSuccess): string
    {
        if ($isSuccess && $levelName === 'INFO') {
            return "\033[1;32m [ INFO ] ✨\033[0m";
        }
        return $this->levelColors[$levelName] ?? $levelName;
    }

    /**
     * Build a colored, local-timezone datetime string for terminal output.
     *
     * The datetime is converted to the server's local timezone so that
     * logs always display the user's local time in the terminal.
     */
    protected function buildColoredDatetime(
        \DateTimeInterface $datetime,
        string $levelName,
        bool $isSuccess
    ): string {
        $localTz     = new \DateTimeZone(date_default_timezone_get());
        $localDt     = \DateTimeImmutable::createFromInterface($datetime)->setTimezone($localTz);
        $datetimeColor = ($isSuccess && $levelName === 'INFO')
            ? "\033[1;32m"
            : ($this->messageColors[$levelName] ?? $this->resetColor);

        return $datetimeColor . '[' . $localDt->format('Y-m-d H:i:s T') . ']' . $this->resetColor;
    }

    protected function colorMessage(string $message, string $levelName): string
    {
        $color = $this->messageColors[$levelName] ?? $this->resetColor;
        return $color . $message . $this->resetColor;
    }
}
