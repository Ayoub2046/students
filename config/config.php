<?php
/**
 * University Lost & Found Management System
 * "Lost Today, Find Tomorrow"
 * Global Application Configuration
 */

// Application Details
if (!defined('APP_NAME')) {
    define('APP_NAME', 'University Lost & Found');
}
if (!defined('APP_SLOGAN')) {
    define('APP_SLOGAN', 'Lost Today, Find Tomorrow');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '2.4.0');
}

// Base URLs and Root Paths (Adjust according to XAMPP folder or virtual host)
// Auto-detect base URL dynamically for flexibility
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('#/(admin|officer|actions.*|includes.*)$#', '', $scriptDir);
$basePath = rtrim($basePath, '/');

if (!defined('APP_URL')) {
    define('APP_URL', $protocol . $hostName . $basePath);
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
}

// Database Credentials (XAMPP Default)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'university_lost_found');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', 3306);
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// Session Settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Timezone
date_default_timezone_set('UTC');

// Error reporting (Production clean, development informative)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');

// Load database connection and constants
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once ROOT_PATH . 'includes/functions.php';
