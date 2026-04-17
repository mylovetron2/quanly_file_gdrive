<?php
/**
 * API: File Upload
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

if (!$permission->can('file.upload')) {
    Helper::jsonResponse(['success' => false, 'message' => 'You do not have permission to upload files'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    Helper::jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error']);
}

try {
    $fileManager = new FileManager();
    
    $folderId = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? (int)$_POST['folder_id'] : null;
    $description = $_POST['description'] ?? '';
    
    $result = $fileManager->uploadFile($_FILES['file'], $folderId, $description);
    
    Helper::jsonResponse($result, 200);
    
} catch (Exception $e) {
    error_log("Upload API error: " . $e->getMessage());
    Helper::jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
