<?php
namespace Bufferpunk\Logmachine;

use Monolog\Logger;
use Bufferpunk\Logmachine\Traits\ColorfulLoggerTrait;

class ColorLogger extends Logger
{
    use ColorfulLoggerTrait;
}

