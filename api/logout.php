<?php
/**
 * API: Logout
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Helper.php';

$auth = new Auth();
$auth->logout();

Helper::redirect(APP_URL . '/views/auth/login.php');
