<?php

// Simulate different log scenarios based on action
if ($action) {
    switch ($action) {
        // Basic log levels
        case 'success':
            $logger->success("Operation completed successfully");
            break;
            
        case 'error':
            $logger->error("Database connection failed");
            break;
            
        case 'warning':
            $logger->warning("API response time exceeded threshold (2.5s)");
            break;
            
        case 'info':
            $logger->info("User login attempt from IP: " . $_SERVER['REMOTE_ADDR']);
            break;
            
        case 'debug':
            $logger->debug("Processing request with parameters: " . json_encode($_GET));
            break;
            
        // Custom messages
        case 'custom':
            $customMsg = $message ?: "Custom log message with no specific level";
            $logger->log("INFO", $customMsg);
            break;
            
        // Simulate user actions
        case 'user_login':
            $logger->success("User 'john_doe' logged in successfully");
            break;
            
        case 'user_logout':
            $logger->info("User 'john_doe' logged out");
            break;
            
        case 'user_register':
            $logger->success("New user registration: john_doe@example.com");
            break;
            
        case 'failed_login':
            $logger->warning("Failed login attempt for user 'admin' from IP: " . $_SERVER['REMOTE_ADDR']);
            break;
            
        // Simulate system events
        case 'cache_clear':
            $logger->info("Application cache cleared by admin");
            break;
            
        case 'maintenance':
            $logger->warning("System entering maintenance mode");
            break;
            
        case 'backup':
            $logger->success("Database backup completed successfully");
            break;
            
        // Simulate API operations
        case 'api_request':
            $logger->info("API request: GET /api/users - Status: 200");
            break;
            
        case 'api_error':
            $logger->error("API error: 500 Internal Server Error - /api/payment");
            break;
            
        // Simulate file operations
        case 'file_upload':
            $logger->success("File uploaded: document.pdf (2.5 MB)");
            break;
            
        case 'file_delete':
            $logger->warning("File deleted: old_backup.zip");
            break;
            
        // Simulate database operations
        case 'db_query':
            $logger->debug("Slow query detected: SELECT * FROM users WHERE last_login > '2024-01-01' (2.3s)");
            break;
            
        case 'db_error':
            $logger->error("Database error: Duplicate entry 'john@example.com' for key 'email'");
            break;
            
        // Simulate security events
        case 'security_alert':
            $logger->error("SECURITY ALERT: Multiple failed login attempts detected from IP: " . $_SERVER['REMOTE_ADDR']);
            break;
            
        case 'csrf_detected':
            $logger->warning("CSRF token validation failed for form submission");
            break;
            
        // Simulate performance metrics
        case 'performance':
            $logger->info("Performance metric: Page load time - 1.23s, Memory usage - 24.5 MB");
            break;
            
        // Simulate email operations
        case 'email_sent':
            $logger->success("Email sent to user@example.com - Welcome email");
            break;
            
        case 'email_failed':
            $logger->error("Failed to send email to invalid@example.com - Invalid recipient");
            break;
            
        // Batch logging test
        case 'batch':
            $logger->info("Starting batch job: Process 100 records");
            $logger->success("Batch job completed: 98 successful, 2 failed");
            break;
            
        // Simulate transaction
        case 'transaction':
            $logger->info("Transaction started: Payment processing");
            $logger->success("Transaction completed: Payment ID: TXN12345");
            break;
            
        // Simulate session events
        case 'session_start':
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $logger->info("New session created: Session ID: " . session_id());
            break;
            
        case 'session_expired':
            $logger->warning("User session expired after 30 minutes of inactivity");
            break;
            
        // Random error simulation
        case 'random_error':
            $errors = [
                "Connection timeout after 30 seconds",
                "Invalid input format for field 'email'",
                "Service unavailable: Payment gateway offline",
                "Rate limit exceeded for API key",
                "Missing required parameter: 'user_id'"
            ];
            $randomError = $errors[array_rand($errors)];
            $logger->error($randomError);
            break;
            
        // JSON payload logging
        case 'json_payload':
            $payload = [
                'event' => 'user_action',
                'user_id' => 12345,
                'action' => 'purchase',
                'items' => ['item1', 'item2'],
                'total' => 99.99
            ];
            $logger->info("Received webhook payload: " . json_encode($payload));
            break;
            
        // Multiple log entries test
        case 'multiple':
            $logger->info("Processing request #1");
            $logger->info("Processing request #2");
            $logger->warning("Request #3 validation failed");
            $logger->success("All requests processed successfully");
            break;
            
        // Default case for unknown actions
        default:
            $logger->info("Unknown action triggered: " . $action);
            break;
    }
    
    // Add a redirect back to the main page after logging
    // This prevents refresh from triggering the same log multiple times
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

?>