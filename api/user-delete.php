<?php
/**
 * API: User Delete
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

$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    Helper::jsonResponse(['success' => false, 'message' => 'Invalid user ID']);
}

// Prevent deleting super admin or yourself
if ($userId == 1) {
    Helper::jsonResponse(['success' => false, 'message' => 'Cannot delete super admin']);
}

if ($userId == $_SESSION['user_id']) {
    Helper::jsonResponse(['success' => false, 'message' => 'Cannot delete yourself']);
}

try {
    $db = Database::getInstance();
    
    // Check if user exists
    $db->query("SELECT username FROM users WHERE id = :id");
    $db->bind(':id', $userId);
    $user = $db->fetch();
    
    if (!$user) {
        Helper::jsonResponse(['success' => false, 'message' => 'User not found']);
    }
    
    // Delete user
    $result = $db->delete('users', ['id' => $userId]);
    
    if ($result) {
        Helper::jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        Helper::jsonResponse(['success' => false, 'message' => 'Failed to delete user']);
    }
    
} catch (Exception $e) {
    error_log("User delete API error: " . $e->getMessage());
    Helper::jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}
