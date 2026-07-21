<?php
/**
 * Global application configuration.
 * NOTE: In production, load these from environment variables (.env)
 * instead of hardcoding. Never commit real credentials to git.
 */

// ---------- App ----------
define('APP_NAME', 'Nomination Form Success Story');
define('APP_URL', 'http://localhost/Nomination-Form-Success-Story'); // no trailing slash, NO /public here
define('APP_ENV', 'development'); // development | production

// ---------- Timezone ----------
date_default_timezone_set('Asia/Kolkata');

// ---------- Database ----------
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'nomination_form_success_story');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---------- SMTP (used by App\Helpers\SmtpMailer) ----------
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);              // 587 = STARTTLS, 465 = SSL
define('SMTP_ENCRYPTION', 'tls');      // tls | ssl
define('SMTP_USERNAME', 'SMTP_EMAIL');
define('SMTP_PASSWORD', 'SMTP_PASSWORD');
define('SMTP_FROM_EMAIL', 'SMTP_EMAIL');
define('SMTP_FROM_NAME', APP_NAME);

// ---------- File Upload ----------
define('UPLOAD_DIR', dirname(__DIR__, 2) . '/uploads/profile_pics/'); // absolute filesystem path
define('UPLOAD_URL', APP_URL . '/uploads/profile_pics/');              // public URL (served directly, real folder)
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2 MB
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOADS_BASE_DIR', dirname(__DIR__, 2) . '/uploads/');

// ---------- Security ----------
define('SESSION_NAME', 'auth_sess');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('PASSWORD_MIN_LENGTH', 8);

// ---------- Error reporting ----------
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
