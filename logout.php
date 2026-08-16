<?php
/**
 * Logout Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::logout();
setFlash('info', 'You have been safely signed out.');
header('Location: ' . APP_URL . '/login.php');
exit;
