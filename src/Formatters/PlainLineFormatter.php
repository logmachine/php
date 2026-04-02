<?php

namespace Bufferpunk\Logmachine\Formatters;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

/**
 * A plain-text formatter for file-based log handlers.
 *
 * Produces clean, alphanumeric log lines with no ANSI escape codes,
 * no emoji characters, and no terminal-specific formatting — suitable
 * for log files that may be read by tools or humans outside a terminal.
 */
class PlainLineFormatter extends LineFormatter
{
    protected array $levelLabels = [
        'DEBUG'     => '[ DEBUG ]',
        'INFO'      => '[ INFO ]',
        'NOTICE'    => '[ NOTICE ]',
        'WARNING'   => '[ WARNING ]',
        'ERROR'     => '[ ERROR ]',
        'CRITICAL'  => '[ CRITICAL ]',
        'ALERT'     => '[ ALERT ]',
        'EMERGENCY' => '[ EMERGENCY ]',
    ];

    public function format(array|LogRecord $record): string
    {
        if ($record instanceof LogRecord) {
            $levelName  = $record->level->getName();
            $levelLabel = $this->levelLabels[$levelName] ?? $levelName;

            $record = $record->with(
                extra: array_merge($record->extra, [
                    'level_colored'    => $levelLabel,
                    'datetime_colored' => '[' . $record->datetime->format('Y-m-d H:i:s T') . ']',
                ])
            );
        } else {
            $levelName  = $record['level_name'];
            $levelLabel = $this->levelLabels[$levelName] ?? $levelName;

            $record['extra']['level_colored']    = $levelLabel;
            $record['extra']['datetime_colored'] = '[' . $record['datetime']->format('Y-m-d H:i:s T') . ']';
        }

        $formatted = parent::format($record);

        // Strip any ANSI escape sequences that may have slipped through
        $formatted = preg_replace('/\x1b\[[0-9;]*[mGKHF]/u', '', $formatted);

        // Strip emoji / non-ASCII supplementary-plane characters (U+1F000 and above)
        $formatted = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $formatted);

        // Collapse any double-spaces that result from stripped characters
        $formatted = preg_replace('/ {2,}/', ' ', $formatted);

        return $formatted;
    }
}
