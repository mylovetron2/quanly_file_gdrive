<?php
/**
 * Main Entry Point
 * Google Drive File Manager
 */

// Define application root
define('APP_ROOT', __DIR__);

// Load configuration
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';

// Load classes
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';
require_once APP_ROOT . '/includes/FileManager.php';
require_once APP_ROOT . '/includes/FolderManager.php';

// Initialize auth
$auth = new Auth();

// Check if logged in
if (!$auth->isLoggedIn()) {
    Helper::redirect(APP_URL . '/views/auth/login.php');
}

// Redirect to dashboard
Helper::redirect(APP_URL . '/views/dashboard/index.php');
