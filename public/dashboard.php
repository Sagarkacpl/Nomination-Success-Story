<?php
/**
 * public/dashboard.php
 * Accessible as /dashboard — only for logged-in users.
 */

require __DIR__ . '/bootstrap.php';

use App\Core\Session;

if (!Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$userName = Session::get('user_name');
$activePage = 'dashboard';

// ---- Greeting + date/time (IST) ----
date_default_timezone_set('Asia/Kolkata');
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Good Morning';
    $greetIcon = '☀️';
} elseif ($hour < 17) {
    $greeting = 'Good Afternoon';
    $greetIcon = '🌤️';
} else {
    $greeting = 'Good Evening';
    $greetIcon = '🌙';
}
$todayDate = date('l, d M Y');
$nowTime = date('h:i:s a') . ' IST';



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php require __DIR__ . '/../views/partials/dashboard-header.php'; ?>

    <div class="dashboard-body">
        <?php require __DIR__ . '/../views/partials/dashboard-sidebar.php'; ?>

        <main class="dashboard-content">

            <!-- Welcome Banner -->
            <div class="welcome-banner mb-4">
                <div class="welcome-banner-text">
                    <h3 class="fw-bold mb-1"><?= $greetIcon ?> <?= $greeting ?>, <?= htmlspecialchars($userName) ?>!
                    </h3>
                    <p class="mb-3 opacity-75">Here's what's happening on your platform today.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="banner-pill"><i class="bi bi-calendar3 me-1"></i><?= $todayDate ?></span>
                        <span class="banner-pill"><i class="bi bi-clock me-1"></i><?= $nowTime ?></span>
                    </div>
                </div>
                <div class="welcome-banner-deco d-none d-lg-flex">
                    <i class="bi bi-stars deco-star deco-star-1"></i>
                    <i class="bi bi-stars deco-star deco-star-2"></i>

                </div>
            </div>

            <!-- Congratulations Banner — Second Round Selection (Design 4: matches new reference) -->
            <div class="congrats-card-v4 mb-4">
                <!-- Decorative flowing gold wave lines (right side background) -->
                <svg class="v4-wave-deco" viewBox="0 0 400 300" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M180,300 C220,220 160,180 220,120 C280,60 250,20 300,0" fill="none" stroke="#d4af37"
                        stroke-width="2" opacity="0.5" />
                    <path d="M210,300 C250,225 190,190 250,130 C310,70 280,25 330,0" fill="none" stroke="#d4af37"
                        stroke-width="2" opacity="0.35" />
                    <path d="M240,300 C280,230 220,200 280,140 C340,80 310,30 360,0" fill="none" stroke="#d4af37"
                        stroke-width="2" opacity="0.25" />
                    <path d="M270,300 C310,235 250,210 310,150 C370,90 340,35 390,0" fill="none" stroke="#d4af37"
                        stroke-width="1.5" opacity="0.18" />
                </svg>
                <span class="v4-dot-pattern" aria-hidden="true"></span>

                <!-- Medal illustration (left, 3D gold look) -->
                <div class="v4-medal-wrap" aria-hidden="true">
                    <svg viewBox="0 0 120 170" class="v4-medal-svg">
                        <polygon points="42,0 60,55 42,42" fill="#b8860b" />
                        <polygon points="78,0 60,55 78,42" fill="#d4af37" />
                        <circle cx="60" cy="105" r="48" fill="url(#v4MedalGrad)" />
                        <circle cx="60" cy="105" r="48" fill="none" stroke="#a8790f" stroke-width="2" />
                        <circle cx="60" cy="105" r="36" fill="none" stroke="#fff8e1" stroke-width="2"
                            stroke-dasharray="4 4" opacity="0.55" />
                        <path d="M60 83 L64.5 96 L78 96 L67 104.5 L71 118 L60 109.5 L49 118 L53 104.5 L42 96 L55.5 96 Z"
                            fill="#ffffff" />
                        <defs>
                            <radialGradient id="v4MedalGrad" cx="35%" cy="30%" r="75%">
                                <stop offset="0%" stop-color="#fde68a" />
                                <stop offset="55%" stop-color="#e8b923" />
                                <stop offset="100%" stop-color="#b8860b" />
                            </radialGradient>
                        </defs>
                    </svg>
                </div>

                <div class="v4-content">
                    <span class="v4-badge"><i class="bi bi-check-circle-fill me-1"></i>Selected</span>
                    <h4 class="fw-bold mb-2">Hello <?= htmlspecialchars($userName) ?>, Congratulations! 🎉</h4>
                    <p class="mb-3">
                        Based on your form submission, you have been selected<br class="d-none d-md-block">
                        for the <strong>Second Round</strong>.<br>
                        Click below to begin your next step — good luck!
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="second-round.php" class="btn v4-btn-gold">
                            <i class="bi bi-send-fill me-2"></i>Start Second Round
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <?php require __DIR__ . '/../views/partials/dashboard-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>

</html>