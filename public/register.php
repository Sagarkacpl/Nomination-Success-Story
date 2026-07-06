<?php
/**
 * public/register.php
 * Accessible as /register (extension hidden by .htaccess)
 */

require __DIR__ . '/bootstrap.php';

use App\Controllers\AuthController;

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->register();
} else {
    $auth->showRegister();
}
