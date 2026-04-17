<?php
/**
 * API: Folder Update
 * Update folder information
 */

// Prevent any output before JSON
ob_start();

try {
    define('APP_ROOT', dirname(__DIR__));
    require_once APP_ROOT . '/config/config.php';
    require_once APP_ROOT . '/config/database.php';
    require_once APP_ROOT . '/includes/Auth.php';
    require_once APP_ROOT . '/includes/Permission.php';
    require_once APP_ROOT . '/includes/Helper.php';
    require_once APP_ROOT . '/includes/FolderManager.php';
    
    // Clear any output from includes
    ob_clean();
    
    header('Content-Type: application/json');
    
    $auth = new Auth();
    
    if (!$auth->isLoggedIn()) {
        Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        exit;
    }
    
    $permission = new Permission();
    
    if (!$permission->can('folder.manage')) {
        Helper::jsonResponse(['success' => false, 'message' => 'Bạn không có quyền quản lý thư mục'], 403);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        exit;
    }
    
    $folderId = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;
    $folderName = Helper::sanitize($_POST['folder_name'] ?? '');
    $description = Helper::sanitize($_POST['description'] ?? '');
    
    if (!$folderId) {
        Helper::jsonResponse(['success' => false, 'message' => 'Thiếu ID thư mục'], 200);
        exit;
    }
    
    if (empty($folderName)) {
        Helper::jsonResponse(['success' => false, 'message' => 'Tên thư mục không được để trống'], 200);
        exit;
    }
    
    $folderManager = new FolderManager();
    
    $data = [
        'folder_name' => $folderName
    ];
    
    if (!empty($description)) {
        $data['description'] = $description;
    }
    
    $result = $folderManager->updateFolder($folderId, $data);
    
    Helper::jsonResponse($result, 200);
    
} catch (Exception $e) {
    ob_clean();
    error_log("Folder update API error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
ob_end_flush();
