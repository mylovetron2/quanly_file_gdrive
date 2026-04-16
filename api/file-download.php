<?php
/**
 * API: File Download
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FileManager.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    die('Unauthorized');
}

$permission = new Permission();

if (!$permission->can('file.download')) {
    die('You do not have permission to download files');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('File ID required');
}

try {
    $fileManager = new FileManager();
    $fileId = (int)$_GET['id'];
    
    $result = $fileManager->downloadFile($fileId);
    
    if (!$result['success']) {
        die('Error: ' . $result['message']);
    }
    
    // Set headers for download
    header('Content-Type: ' . $result['mime_type']);
    header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
    header('Content-Length: ' . strlen($result['content']));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output file content
    echo $result['content'];
    exit;
    
} catch (Exception $e) {
    error_log("Download API error: " . $e->getMessage());
    die('Server error occurred');
}
