<?php
/**
 * public/verify.php
 * Accessible as /verify?token=xxxx (link sent via email)
 */

require __DIR__ . '/bootstrap.php';

use App\Controllers\AuthController;

(new AuthController())->verifyEmail();
