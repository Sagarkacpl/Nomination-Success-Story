<?php

namespace App\Core;

/**
 * Session
 * Centralised, hardened session bootstrap + CSRF token helpers.
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(SESSION_NAME);

        // Harden session cookie
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => APP_ENV === 'production', // true only when served over HTTPS
            'httponly' => true,                      // JS cannot read the cookie (XSS mitigation)
            'samesite' => 'Lax',                      // CSRF mitigation
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_start();

        // Basic session fixation guard
        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = time();
        } elseif (time() - $_SESSION['_created_at'] > 1800) {
            // regenerate id every 30 minutes
            session_regenerate_id(true);
            $_SESSION['_created_at'] = time();
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // ---------------- CSRF ----------------

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }
}
