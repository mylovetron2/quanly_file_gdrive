<?php
/**
 * API: Folder Delete
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FolderManager.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$permission = new Permission();

if (!$permission->can('folder.delete')) {
    Helper::jsonResponse(['success' => false, 'message' => 'You do not have permission to delete folders'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

if (!isset($_POST['folder_id']) || empty($_POST['folder_id'])) {
    Helper::jsonResponse(['success' => false, 'message' => 'Folder ID required']);
}

try {
    $folderManager = new FolderManager();
    $folderId = (int)$_POST['folder_id'];
    
    $result = $folderManager->deleteFolder($folderId);
    
    Helper::jsonResponse($result, $result['success'] ? 200 : 400);
    
} catch (Exception $e) {
    error_log("Folder delete API error: " . $e->getMessage());
    Helper::jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
