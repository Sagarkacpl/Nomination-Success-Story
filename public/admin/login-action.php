<?php
/**
 * public/admin/login-action.php
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;
use App\Models\Admin;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: index.php');
    exit;
}


$csrfToken = $_POST['csrf_token'] ?? '';
if (!Session::verifyCsrf($csrfToken)) {
    Session::set('admin_flash_error', 'Session expired. Please try again.');
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    Session::set('admin_flash_error', 'Email and password are required.');
    header('Location: index.php');
    exit;
}

$adminModel = new Admin();
$admin = $adminModel->findByEmail($email);

// Same generic error whether email doesn't exist or password is wrong (avoid enumeration)
if (!$admin || !password_verify($password, $admin['password_hash'])) {
    Session::set('admin_flash_error', 'Invalid email or password.');
    header('Location: index.php');
    exit;
}

// Prevent session fixation on privilege change
Session::regenerate();
Session::set('admin_id', $admin['id']);
Session::set('admin_name', $admin['full_name']);

header('Location: dashboard.php');
exit;