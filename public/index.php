<?php
/**
 * public/index.php
 * This IS the login page. Visiting "/" (root URL) shows the login form.
 */

require __DIR__ . '/bootstrap.php';

use App\Core\Session;
use App\Controllers\AuthController;

// Already logged in? send them to the dashboard instead of showing login again.
if (Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard');
    exit;
}

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
} else {
    $auth->showLogin();
}
