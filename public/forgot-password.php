<?php
/**
 * public/forgot-password.php
 * Accessible as /forgot-password — only for logged-OUT users.
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Session;

// Agar already logged in hai to dashboard bhej do
if (Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new \App\Controllers\AuthController())->forgotPassword();
    return;
}

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

            <h6 class="text-muted mb-1">Forgot your password?</h6>
            <p class="text-muted small mb-3">
                Enter the email address linked to your account. If it exists, we'll send you a link to reset your
                password.
            </p>

            <?php require __DIR__ . '/../views/partials/flash.php'; ?>

            <form action="forgot-password" method="POST" id="forgotPasswordForm" novalidate autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button type="submit" class="btn text-white flex-fill" style="background:#8e1e8e;">Send Reset
                        Link</button>
                </div>

                <p class="text-muted small mb-0 text-center">
                    Remembered your password? <a href="index.php" class="text-decoration-none">Back to login</a>.
                </p>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../views/partials/footer.php'; ?>