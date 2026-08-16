<?php
/**
 * Role Verification Functions
 */
require_once __DIR__ . '/auth-check.php';

function requireLogin(): void {
    if (!Auth::check()) {
        setFlash('danger', 'You must be logged in to view this resource.');
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireRole($roles): void {
    requireLogin();
    $currentRole = Auth::role();

    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array($currentRole, $allowed)) {
        http_response_code(403);
        require_once ROOT_PATH . '403.php';
        exit;
    }
}
