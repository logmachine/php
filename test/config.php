<?php
// Simple PHP web app for log testing

require_once __DIR__ . '/vendor/autoload.php';

use Bufferpunk\Logmachine\LogMachine;

// Create logger instance
$config = require __DIR__ . '/config/logmachine.php';

$logger = LogMachine::create($config);

// Get the current action from URL parameter
$action = $_GET['action'] ?? null;
$message = $_GET['message'] ?? null;

include("simulations.php");

// Track page visit
$logger->debug("Page visited: " . $_SERVER['REQUEST_URI'] . " from IP: " . $_SERVER['REMOTE_ADDR']);

?>
