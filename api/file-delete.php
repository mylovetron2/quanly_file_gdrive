<?php
/**
 * API: File Delete
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FileManager.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$permission = new Permission();

if (!$permission->can('file.delete')) {
    Helper::jsonResponse(['success' => false, 'message' => 'You do not have permission to delete files'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

if (!isset($_POST['file_id']) || empty($_POST['file_id'])) {
    Helper::jsonResponse(['success' => false, 'message' => 'File ID required']);
}

try {
    $fileManager = new FileManager();
    $fileId = (int)$_POST['file_id'];
    
    $result = $fileManager->deleteFile($fileId);
    
    Helper::jsonResponse($result, $result['success'] ? 200 : 400);
    
} catch (Exception $e) {
    error_log("Delete API error: " . $e->getMessage());
    Helper::jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
