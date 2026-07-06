<?php
/**
 * Minimal PSR-4-ish autoloader.
 * Maps namespace prefix "App\" -> /app directory.
 * (If you prefer, swap this out for Composer's vendor/autoload.php.)
 */
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return; // not our namespace
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
