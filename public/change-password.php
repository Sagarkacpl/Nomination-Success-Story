<?php
/**
 * public/change-password.php
 * Accessible as /change-password — only for logged-in users.
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Session;

if (!Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$userName = Session::get('user_name');
$userId = (int) Session::get('user_id');
$activePage = 'change-password';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <title>Change Password - <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php require __DIR__ . '/../views/partials/dashboard-header.php'; ?>

    <div class="dashboard-body">
        <?php require __DIR__ . '/../views/partials/dashboard-sidebar.php'; ?>

        <main class="dashboard-content">
            <div class="content-card mb-3" style="max-width:560px; margin:0 auto;">

                <div class="form-page-header mb-4">
                    <h3 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Change Password
                    </h3>
                    <p class="text-muted mb-0">Choose a strong password you haven't used recently.</p>
                </div>

                <div id="pwdAlert" class="alert d-none" role="alert"></div>

                <form id="changePasswordForm" novalidate autocomplete="off">
                    <input type="hidden" id="csrf_token" value="<?= htmlspecialchars(Session::csrfToken()) ?>">

                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required autocomplete="current-password">
                            <span class="input-group-text bg-white toggle-password" data-target="current_password"
                                style="cursor:pointer;">
                                <i class="bi bi-eye text-muted"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required
                                autocomplete="new-password" minlength="<?= PASSWORD_MIN_LENGTH ?>">
                            <span class="input-group-text bg-white toggle-password" data-target="new_password"
                                style="cursor:pointer;">
                                <i class="bi bi-eye text-muted"></i>
                            </span>
                        </div>
                        <div class="form-text">
                            At least <?= PASSWORD_MIN_LENGTH ?> characters, with uppercase, lowercase, a number, and a
                            special character.
                        </div>
                    </div>

                    <!-- Live strength meter -->
                    <div class="pwd-strength-bar mb-3">
                        <div id="pwdStrengthFill" class="pwd-strength-fill"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required autocomplete="new-password">
                            <span class="input-group-text bg-white toggle-password" data-target="confirm_password"
                                style="cursor:pointer;">
                                <i class="bi bi-eye text-muted"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit-nomination w-100" id="pwdSubmitBtn">
                        <i class="bi bi-check2-circle me-2"></i>Update Password
                    </button>
                </form>

            </div>
        </main>
    </div>

    <?php require __DIR__ . '/../views/partials/dashboard-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/change-password.js?v=<?= filemtime(__DIR__ . '/assets/js/change-password.js') ?>"></script>
</body>

</html>