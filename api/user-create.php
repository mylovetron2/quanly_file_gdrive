<?php
/**
 * API: User Create
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Helper.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    Helper::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if (!$auth->isAdmin()) {
    Helper::jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$username = Helper::sanitize($_POST['username'] ?? '');
$email = Helper::sanitize($_POST['email'] ?? '');
$fullName = Helper::sanitize($_POST['full_name'] ?? '');
$password = $_POST['password'] ?? '';
$roleId = (int)($_POST['role_id'] ?? 5);

if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
    Helper::jsonResponse(['success' => false, 'message' => 'All fields are required']);
}

if (!Helper::validateEmail($email)) {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid email format']);
}

$passwordValidation = Helper::validatePassword($password);
if (!$passwordValidation['valid']) {
    Helper::jsonResponse(['success' => false, 'message' => $passwordValidation['message']]);
}

try {
    $result = $auth->register($username, $email, $password, $fullName, $roleId);
    Helper::jsonResponse($result, $result['success'] ? 200 : 400);
    
} catch (Exception $e) {
    error_log("User create API error: " . $e->getMessage());
    Helper::jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}
