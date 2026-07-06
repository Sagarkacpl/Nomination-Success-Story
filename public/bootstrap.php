<?php
/**
 * bootstrap.php
 * Included at the top of every file in public/ so we don't repeat
 * autoload + config + session + security-headers setup everywhere.
 */

require dirname(__DIR__) . '/autoload.php';
require dirname(__DIR__) . '/app/Config/config.php';

use App\Core\Session;

Session::start();

// Basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

