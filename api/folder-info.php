<?php
/**
 * API: Folder Info
 * Get folder information by ID
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FolderManager.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$permission = new Permission();

if (!$permission->can('folder.manage')) {
    Helper::jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$folderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$folderId) {
    Helper::jsonResponse(['success' => false, 'message' => 'Folder ID required']);
}

try {
    $folderManager = new FolderManager();
    $folder = $folderManager->getFolder($folderId);
    
    if (!$folder) {
        Helper::jsonResponse(['success' => false, 'message' => 'Folder not found']);
    }
    
    Helper::jsonResponse([
        'success' => true,
        'data' => $folder
    ]);
    
} catch (Exception $e) {
    error_log("Folder info API error: " . $e->getMessage());
    Helper::jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}
