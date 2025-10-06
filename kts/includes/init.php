<?php
declare(strict_types=1);

// Initialize app configuration and session
$config = require __DIR__ . '/../config/config.php';

if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Kolkata');
}

session_name($config['app']['session_name']);
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => $config['app']['cookie_secure'],
    'cookie_samesite' => 'Lax',
]);

$GLOBALS['app_config'] = $config;

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($config['app']['base_url'], '/'));
}
