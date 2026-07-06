<?php
/**
 * public/error.php
 * Custom error page — called via Apache's ErrorDocument directive.
 * Usage: ErrorDocument 404 /error.php?code=404
 */
require __DIR__ . '/bootstrap.php';
// Apache passes the original error code via query string; default to 404
// if this file is opened directly without one.
$code = (int) ($_GET['code'] ?? 404);

$errors = [
    400 => [
        'title' => 'Bad Request',
        'message' => 'The request could not be understood by the server. Please check the URL and try again.',
        'icon' => 'bi-exclamation-triangle-fill',
    ],
    401 => [
        'title' => 'Unauthorized',
        'message' => 'You need to be logged in to access this page.',
        'icon' => 'bi-lock-fill',
    ],
    403 => [
        'title' => 'Access Forbidden',
        'message' => 'You don\'t have permission to access this resource.',
        'icon' => 'bi-shield-lock-fill',
    ],
    404 => [
        'title' => 'Page Not Found',
        'message' => 'The page you\'re looking for doesn\'t exist or may have been moved.',
        'icon' => 'bi-signpost-2-fill',
    ],
    500 => [
        'title' => 'Server Error',
        'message' => 'Something went wrong on our end. Please try again in a little while.',
        'icon' => 'bi-exclamation-octagon-fill',
    ],
    503 => [
        'title' => 'Service Unavailable',
        'message' => 'The site is temporarily unavailable. Please check back shortly.',
        'icon' => 'bi-hourglass-split',
    ],
];

$error = $errors[$code] ?? [
    'title' => 'Unexpected Error',
    'message' => 'Something unexpected happened. Please go back to the homepage.',
    'icon' => 'bi-question-circle-fill',
];

// Best-effort: keep the real HTTP status code correct for crawlers/browsers,
// even though Apache already set it before invoking this ErrorDocument.
if (!headers_sent()) {
    http_response_code($code >= 400 && $code < 600 ? $code : 404);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $code ?> - <?= htmlspecialchars($error['title']) ?> | <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            margin: 0;
        }

        .error-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #6a2fb0, #8e1e8e, #d4af37);
        }

        .error-icon-box {
            width: 84px;
            height: 84px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6a2fb0, #8e1e8e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #fff;
        }

        .error-code {
            font-size: 52px;
            font-weight: 800;
            color: #1a0b2e;
            line-height: 1;
            margin-bottom: 6px;
        }

        .error-title {
            font-size: 18px;
            font-weight: 700;
            color: #3a2e42;
            margin-bottom: 10px;
        }

        .error-message {
            color: #8a7f92;
            font-size: 14.5px;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .btn-home {
            background: linear-gradient(135deg, #6a2fb0, #8e1e8e);
            color: #fff;
            border: none;
            padding: 11px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14.5px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-home:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(142, 30, 142, 0.3);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="error-icon-box">
            <i class="bi <?= htmlspecialchars($error['icon']) ?>"></i>
        </div>
        <div class="error-code"><?= $code ?></div>
        <div class="error-title"><?= htmlspecialchars($error['title']) ?></div>
        <p class="error-message"><?= htmlspecialchars($error['message']) ?></p>
        <a href="<?= htmlspecialchars(APP_URL) ?>/" class="btn-home">
            <i class="bi bi-house-door-fill me-1"></i>Go to Homepage
        </a>
    </div>
</body>

</html>