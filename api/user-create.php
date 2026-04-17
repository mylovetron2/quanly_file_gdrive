<?php
/**
 * API: User Create
 */

// Prevent any output before JSON
ob_start();

try {
    define('APP_ROOT', dirname(__DIR__));
    require_once APP_ROOT . '/config/config.php';
    require_once APP_ROOT . '/config/database.php';
    require_once APP_ROOT . '/includes/Auth.php';
    require_once APP_ROOT . '/includes/Helper.php';
    
    // Clear any output from includes
    ob_clean();
    
    header('Content-Type: application/json');
    
    $auth = new Auth();
    
    if (!$auth->isLoggedIn()) {
        Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        exit;
    }
    
    if (!$auth->isAdmin()) {
        Helper::jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        exit;
    }
    
    $username = Helper::sanitize($_POST['username'] ?? '');
    $email = Helper::sanitize($_POST['email'] ?? '');
    $fullName = Helper::sanitize($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 5);
    
    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        Helper::jsonResponse(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!Helper::validateEmail($email)) {
        Helper::jsonResponse(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    $passwordValidation = Helper::validatePassword($password);
    if (!$passwordValidation['valid']) {
        Helper::jsonResponse(['success' => false, 'message' => $passwordValidation['message']]);
        exit;
    }
    
    $result = $auth->register($username, $email, $password, $fullName, $roleId);
    Helper::jsonResponse($result, 200); // Always return 200, let client check success field
    
} catch (Exception $e) {
    ob_clean();
    error_log("User create API error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
ob_end_flush();
