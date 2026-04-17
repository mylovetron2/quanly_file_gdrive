<?php
/**
 * Get Google Drive Storage Quota Information
 * Returns storage limit, usage, and available space
 */

ob_start();

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';

header('Content-Type: application/json');

try {
    // Check authentication
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        ob_end_clean();
        Helper::jsonResponse([
            'success' => false,
            'message' => 'Chưa đăng nhập'
        ], 200);
    }
    
    // Initialize Google Drive API
    try {
        $driveAPI = new GoogleDriveAPI();
        
        // Check if authenticated with Google Drive
        if (!$driveAPI->isAuthenticated()) {
            ob_end_clean();
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Chưa kết nối với Google Drive. Vui lòng upload file để kết nối.',
                'data' => [
                    'success' => false,
                    'authenticated' => false
                ]
            ], 200);
        }
        
        // Get storage quota
        $result = $driveAPI->getStorageQuota();
        
        ob_end_clean();
        
        if ($result['success']) {
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Lấy thông tin dung lượng thành công',
                'data' => $result
            ], 200);
        } else {
            // Check if error is due to insufficient scope
            $errorMsg = $result['error'];
            if (stripos($errorMsg, 'insufficient') !== false || 
                stripos($errorMsg, 'scope') !== false || 
                stripos($errorMsg, 'permission') !== false) {
                $errorMsg = 'Cần thêm quyền truy cập. Vui lòng kết nối lại Google Drive.';
            }
            Helper::jsonResponse([
                'success' => false,
                'message' => $errorMsg,
                'data' => $result
            ], 200);
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        error_log("Drive API initialization error: " . $e->getMessage());
        Helper::jsonResponse([
            'success' => false,
            'message' => 'Chưa kết nối với Google Drive. Vui lòng upload file để kết nối.',
            'data' => [
                'success' => false,
                'authenticated' => false,
                'error' => $e->getMessage()
            ]
        ], 200);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Drive storage API error: " . $e->getMessage());
    Helper::jsonResponse([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ], 200);
}
