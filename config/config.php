<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found. Please create one based on .env.example");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key-value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load the .env file
loadEnv(__DIR__ . '/../.env');

// Helper function to get environment variables
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Define constants from environment variables
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'blog_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

define('APP_NAME', env('APP_NAME', 'Blog Application'));
define('APP_URL', env('APP_URL', 'http://localhost/blog-app'));
define('SESSION_NAME', env('SESSION_NAME', 'blog_session'));
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 3600));

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
?>