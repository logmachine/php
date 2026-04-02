<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use Bufferpunk\Logmachine\LogMachine;

$config = require __DIR__ . '/../config/logmachine.php';

$logger = LogMachine::create($config);


$logger->success("Everything worked great!");
$logger->debug("Watch out for this.");
$logger->emergency("Something went terribly wrong!");
$logger->infoColor("Just an FYI.");
$logger->notice('Payment failed', ['order_id' => 1234]);

$logger->info('User logged in', ['user' => 'jdoe']);
$logger->warning('Disk space low', ['disk' => '/dev/sda1']);
$logger->error('Payment failed', ['order_id' => 1234]);
$logger->critical('Payment failed', ['order_id' => 1234]);
$logger->alert('Payment failed', ['order_id' => 1234]);
$logger->notice("Inform me about this");

header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'Logs sent!',
    'timestamp' => date('c')
]);
