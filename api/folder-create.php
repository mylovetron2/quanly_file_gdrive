<?php
/**
 * API: Folder Create
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FolderManager.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    $_SESSION['error_message'] = 'Unauthorized';
    Helper::redirect(APP_URL . '/views/auth/login.php');
}

$permission = new Permission();

if (!$permission->can('folder.create')) {
    $_SESSION['error_message'] = 'You do not have permission to create folders';
    Helper::redirect(APP_URL . '/views/dashboard/index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method';
    Helper::redirect(APP_URL . '/views/dashboard/index.php');
}

if (!isset($_POST['folder_name']) || empty($_POST['folder_name'])) {
    $_SESSION['error_message'] = 'Folder name required';
    Helper::redirect(APP_URL . '/views/dashboard/files.php');
}

try {
    $folderManager = new FolderManager();
    
    $folderName = Helper::sanitize($_POST['folder_name']);
    $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
    
    $result = $folderManager->createFolder($folderName, $parentId);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
    
    $redirectUrl = APP_URL . '/views/dashboard/files.php';
    if ($parentId) {
        $redirectUrl .= '?folder=' . $parentId;
    }
    
    Helper::redirect($redirectUrl);
    
} catch (Exception $e) {
    error_log("Folder create API error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Server error occurred';
    Helper::redirect(APP_URL . '/views/dashboard/files.php');
}
