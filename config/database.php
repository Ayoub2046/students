<?php
/**
 * University Lost & Found Management System
 * Database Connection via PDO
 */

require_once __DIR__ . '/../classes/Database.php';

try {
    $dbInstance = Database::getInstance();
    $pdo = $dbInstance->getConnection();
} catch (Exception $e) {
    // Graceful error logging without exposing credentials to user
    error_log("Database connection error: " . $e->getMessage());
    $pdo = null;
}
