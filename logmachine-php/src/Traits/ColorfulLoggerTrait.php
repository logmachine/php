<?php
namespace Bufferpunk\Logmachine\Traits;

trait ColorfulLoggerTrait
{
    public function success(string $message, array $context = []): void
    {
        $context['_success'] = true;
        $this->info($this->colorize($message, 'green'), $context);
    }

    public function warn(string $message, array $context = []): void
    {
        $this->warning($this->colorize($message, 'yellow'), $context);
    }

    public function danger(string $message, array $context = []): void
    {
        $this->error($this->colorize($message, 'red'), $context);
    }

    public function infoColor(string $message, array $context = []): void
    {
        $this->info($this->colorize($message, 'blue'), $context);
    }

    protected function colorize(string $message, string $color): string
    {
        $colors = [
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'red' => "\033[31m",
            'blue' => "\033[34m",
            'reset' => "\033[0m",
        ];

        $colorCode = $colors[$color] ?? $colors['reset'];
        return $colorCode . $message . $colors['reset'];
    }
}

