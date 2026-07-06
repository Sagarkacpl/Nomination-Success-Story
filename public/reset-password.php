<?php
/**
 * public/reset-password.php
 * Accessible as /reset-password?token=... — only for logged-OUT users
 * with a valid, non-expired reset token.
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Session;
use App\Models\User;

if (Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new \App\Controllers\AuthController())->resetPassword();
    return;
}

$token = $_GET['token'] ?? '';
$userModel = new User();
$validUser = !empty($token) ? $userModel->findByValidResetToken($token) : null;

$csrf_token = Session::csrfToken();
?>
<?php require __DIR__ . '/../views/partials/header.php'; ?>

<div class="split-container">
    <div class="split-left d-none d-lg-flex">
        <div class="left-content">
            <img src="assets/images/icai-75-logo.jpg" alt="ICAI" height="60" class="mb-3">
            <h5 class="text-white-50 fw-normal">The Institute of Chartered Accountants of India</h5>
            <p class="text-white-50 small">(Set up by an Act of Parliament)</p>
            <h6 class="text-white-50">Women &amp; Young Members Excellence Committee (WYMEC)</h6>
            <h1 class="display-7 fw-bold text-gold">Invitation for Nomination - Udaan <span class="text-white">-
                    Celebrating Womanhood - to recognise and document the inspiring journeys of women CAs</span></h1>
        </div>
    </div>

    <div class="split-right">
        <div class="form-card">
            <div class="text-center mb-4">
                <img src="assets/images/icai-75-logo.jpg" alt="ICAI" height="55" class="mb-2">
                <h5 class="fw-bold mb-0">The Institute of Chartered Accountants of India</h5>
                <p class="text-muted small mb-1">(Set up by an Act of Parliament)</p>
                <p class="fw-semibold small mb-0">Women &amp; Young Members Excellence Committee (WYMEC) of ICAI</p>
                <p class="small text-muted mt-2 mb-0 px-2" style="line-height: 1.4;">
                    Invitation for Nomination - Udaan - Celebrating Womanhood - to recognise and document the inspiring
                    journeys of women CAs
                </p>
            </div>

            <?php if (!$validUser): ?>

                <h6 class="text-danger mb-3 text-center">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>Invalid or Expired Link
                </h6>
                <p class="text-muted small mb-3">
                    This password reset link is invalid or has expired. Please request a new one.
                </p>

                <?php require __DIR__ . '/../views/partials/flash.php'; ?>

                <div class="d-flex gap-2 mb-3">
                    <a href="forgot-password" class="btn text-white flex-fill" style="background:#8e1e8e;">Request New
                        Link</a>
                </div>

                <p class="text-muted small mb-0 text-center">
                    Remembered your password? <a href="index.php" class="text-decoration-none">Back to login</a>.
                </p>

            <?php else: ?>

                <h6 class="text-muted mb-1">Set a new password</h6>
                <p class="text-muted small mb-3">
                    Choose a new password for <strong>
                        <?= htmlspecialchars($validUser['email']) ?>
                    </strong>.
                </p>

                <?php require __DIR__ . '/../views/partials/flash.php'; ?>

                <form action="reset-password?token=<?= urlencode($token) ?>" method="POST" id="resetPasswordForm" novalidate
                    autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group input-group-lg">
                            <input type="password" class="form-control" id="password" name="password" required
                                minlength="8">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group input-group-lg">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required minlength="8">
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="confirm_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="submit" class="btn text-white flex-fill" style="background:#8e1e8e;">Reset
                            Password</button>
                    </div>

                    <p class="text-muted small mb-0 text-center">
                        Remembered your password? <a href="index.php" class="text-decoration-none">Back to login</a>.
                    </p>
                </form>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../views/partials/footer.php'; ?>