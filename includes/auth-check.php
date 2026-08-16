<?php
/**
 * Authentication Gatekeeper
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';

if (!Auth::check()) {
    setFlash('warning', 'Please log in to access this page.');
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$currentUser = Auth::user();
