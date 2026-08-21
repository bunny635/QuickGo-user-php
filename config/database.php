<?php
// config/database.php

// Define environment variables / constants
define('DB_HOST', '127.0.0.1'); // Using 127.0.0.1 avoids DNS resolution overhead
define('DB_NAME', 'quickgo_db');
define('DB_USER', 'root');      // Update with your actual MySQL user
define('DB_PASS', '');          // Update with your actual MySQL password

try {
    // Create DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    // PDO Configuration Options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays natively
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Enforce real prepared statements for security
        PDO::ATTR_PERSISTENT         => true                    // Use persistent connections for performance
    ];

    // Establish Connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Fail gracefully in production, do not leak credentials
    // Log the actual error internally: error_log($e->getMessage());
    die("A database connection error occurred. Please try again later.");
}
