<?php
/**
 * Health check endpoint for Railway
 * Returns 200 if the application is running
 */

// Disable error display for clean JSON response
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');

$status = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION
];

// Check database connection if configured
try {
    $envPath = dirname(__DIR__) . '/.env';
    
    // Load env if file exists
    if (file_exists($envPath)) {
        require_once dirname(__DIR__) . '/config/env.php';
        Env::load();
    }
    
    $dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: null;
    
    if ($dbHost) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $dbHost,
            $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306',
            $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'kastasso',
            $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4'
        );
        
        $pdo = new PDO(
            $dsn,
            $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
            $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
            [PDO::ATTR_TIMEOUT => 5]
        );
        
        $status['database'] = 'connected';
    } else {
        $status['database'] = 'not_configured';
    }
} catch (Exception $e) {
    $status['database'] = 'error';
    $status['database_error'] = $e->getMessage();
}

http_response_code(200);
echo json_encode($status, JSON_PRETTY_PRINT);
