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

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
}

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    if ($isAjax) {
        Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    } else {
        $_SESSION['error_message'] = 'Unauthorized';
        Helper::redirect(APP_URL . '/views/auth/login.php');
    }
}

$permission = new Permission();

if (!$permission->can('folder.create')) {
    if ($isAjax) {
        Helper::jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
    } else {
        $_SESSION['error_message'] = 'You do not have permission to create folders';
        Helper::redirect(APP_URL . '/views/dashboard/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
    } else {
        $_SESSION['error_message'] = 'Invalid request method';
        Helper::redirect(APP_URL . '/views/dashboard/index.php');
    }
}

if (!isset($_POST['folder_name']) || empty($_POST['folder_name'])) {
    if ($isAjax) {
        Helper::jsonResponse(['success' => false, 'message' => 'Folder name required']);
    } else {
        $_SESSION['error_message'] = 'Folder name required';
        Helper::redirect(APP_URL . '/views/dashboard/files.php');
    }
}

try {
    $folderManager = new FolderManager();
    
    $folderName = Helper::sanitize($_POST['folder_name']);
    $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
    
    $result = $folderManager->createFolder($folderName, $parentId);
    
    if ($isAjax) {
        Helper::jsonResponse($result, 200);
    } else {
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
    }
    
} catch (Exception $e) {
    error_log("Folder create API error: " . $e->getMessage());
    if ($isAjax) {
        Helper::jsonResponse(['success' => false, 'message' => 'Server error'], 500);
    } else {
        $_SESSION['error_message'] = 'Server error occurred';
        Helper::redirect(APP_URL . '/views/dashboard/files.php');
    }
}
