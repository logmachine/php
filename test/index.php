<?php
    include __DIR__ . '/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Log Testing Suite</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <h1>PHP Log Testing Suite</h1>
    <p class="subtitle">Test and visualize different logging scenarios</p>
</header>

<div class="info-box">
    <strong>Info:</strong> Check your logs at <code>config/logs/all_logs.log</code> and <code>config/logs/error_logs.log</code> (or the paths configured in <code>config.php</code>).
    Each action generates log entries with different severity levels.
</div>

<main class="container">

    <section class="section">
        <h2>Basic Log Levels</h2>
        <ul>
            <li><a href="?action=success&redirect=false" class="btn success">Success Log</a></li>
            <li><a href="?action=error&redirect=false" class="btn error">Error Log</a></li>
            <li><a href="?action=warning&redirect=false" class="btn warning">Warning Log</a></li>
            <li><a href="?action=info&redirect=false" class="btn info">Info Log</a></li>
            <li><a href="?action=debug&redirect=false" class="btn debug">Debug Log</a></li>
            <li><a href="?action=custom&message=My%20custom%20message&redirect=false" class="btn">Custom Log</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>User Actions</h2>
        <ul>
            <li><a href="?action=user_login&redirect=false" class="btn">User Login</a></li>
            <li><a href="?action=user_logout&redirect=false" class="btn">User Logout</a></li>
            <li><a href="?action=user_register&redirect=false" class="btn">User Registration</a></li>
            <li><a href="?action=failed_login&redirect=false" class="btn warning">Failed Login</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>System Events</h2>
        <ul>
            <li><a href="?action=cache_clear&redirect=false" class="btn">Clear Cache</a></li>
            <li><a href="?action=maintenance&redirect=false" class="btn">Maintenance Mode</a></li>
            <li><a href="?action=backup&redirect=false" class="btn">Database Backup</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>API Operations</h2>
        <ul>
            <li><a href="?action=api_request&redirect=false" class="btn">API Request</a></li>
            <li><a href="?action=api_error&redirect=false" class="btn error">API Error</a></li>
            <li><a href="?action=json_payload&redirect=false" class="btn">JSON Payload</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>Database Operations</h2>
        <ul>
            <li><a href="?action=db_query&redirect=false" class="btn warning">Slow Query</a></li>
            <li><a href="?action=db_error&redirect=false" class="btn error">Database Error</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>Security Events</h2>
        <ul>
            <li><a href="?action=security_alert&redirect=false" class="btn error">Security Alert</a></li>
            <li><a href="?action=csrf_detected&redirect=false" class="btn warning">CSRF Detection</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>Performance & Testing</h2>
        <ul>
            <li><a href="?action=performance&redirect=false" class="btn">Performance Metrics</a></li>
            <li><a href="?action=batch&redirect=false" class="btn">Batch Processing</a></li>
            <li><a href="?action=multiple&redirect=false" class="btn">Multiple Logs</a></li>
            <li><a href="?action=random_error&redirect=false" class="btn warning">Random Error</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>Email & Transactions</h2>
        <ul>
            <li><a href="?action=email_sent&redirect=false" class="btn success">Email Sent</a></li>
            <li><a href="?action=email_failed&redirect=false" class="btn error">Email Failed</a></li>
            <li><a href="?action=transaction&redirect=false" class="btn">Transaction</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>Session Management</h2>
        <ul>
            <li><a href="?action=session_start&redirect=false" class="btn">Session Start</a></li>
            <li><a href="?action=session_expired&redirect=false" class="btn warning">Session Expired</a></li>
        </ul>
    </section>

    <section class="section">
        <h2>File Operations</h2>
        <ul>
            <li><a href="?action=file_upload&redirect=false" class="btn">File Upload</a></li>
            <li><a href="?action=file_delete&redirect=false" class="btn error">File Delete</a></li>
        </ul>
    </section>

</main>

<footer class="footer">
    <p>PHP Log Testing Suite | <a href="?action=debug&redirect=false">Refresh Page</a></p>
</footer>

</body>
</html>