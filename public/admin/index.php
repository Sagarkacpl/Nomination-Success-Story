<?php
/**
 * public/admin/index.php
 * Admin login page — accessible as /admin
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;

// Agar already admin logged in hai to seedha dashboard bhej do
if (Session::get('admin_id')) {
    header('Location: dashboard.php');
    exit;
}

$csrf_token = Session::csrfToken();
$flashError = Session::get('admin_flash_error');
Session::set('admin_flash_error', null);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/favicon.png">
    <title>Admin Login -
        <?= htmlspecialchars(APP_NAME) ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="split-container">
        <div class="split-left d-none d-lg-flex">
            <div class="left-content">
                <img src="../assets/images/icai-75-logo.jpg" alt="ICAI" height="60" class="mb-3">
                <h5 class="text-white-50 fw-normal">The Institute of Chartered Accountants of India</h5>
                <p class="text-white-50 small">(Set up by an Act of Parliament)</p>
                <h6 class="text-white-50">Women &amp; Young Members Excellence Committee (WYMEC)</h6>
                <h1 class="display-5 fw-bold text-gold">Admin <span class="text-white">Control Panel</span></h1>
            </div>
        </div>

        <div class="split-right">
            <div class="form-card">
                <div class="text-center mb-4">
                    <img src="../assets/images/icai-75-logo.jpg" alt="ICAI" height="55" class="mb-2">
                    <h5 class="fw-bold mb-0">The Institute of Chartered Accountants of India</h5>
                    <p class="text-muted small mb-1">(Set up by an Act of Parliament)</p>
                    <p class="fw-semibold small mb-0">Women &amp; Young Members Excellence Committee (WYMEC)</p>
                    <h4 class="fw-bold mt-2" style="color:#8e1e8e;">Admin Panel</h4>
                </div>

                <h6 class="text-muted mb-3">Please sign in</h6>

                <?php if ($flashError): ?>
                    <div class="alert alert-danger py-2" role="alert">
                        <?= htmlspecialchars($flashError) ?>
                    </div>
                <?php endif; ?>

                <form action="login-action" method="POST" id="adminLoginForm" novalidate autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required
                            placeholder="Enter your admin email">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                            required placeholder="Enter your password">
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="submit" class="btn text-white flex-fill" style="background:#8e1e8e;">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>