<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpn_panel');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'VPN Management Panel');
define('APP_URL', 'http://localhost');
define('APP_VERSION', '1.0.0');

// Security Settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['ovpn', 'conf', 'key', 'crt', 'pem']);

// Currency Settings
define('DEFAULT_CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
