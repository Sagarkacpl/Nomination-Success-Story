<?php require __DIR__ . '/../partials/header.php'; ?>

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

            <h6 class="text-muted mb-3">Please sign in</h6>

            <?php require __DIR__ . '/../partials/flash.php'; ?>

            <form action="./" method="POST" id="loginForm" novalidate autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group input-group-lg">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button type="submit" class="btn text-white flex-fill" style="background:#8e1e8e;">Login</button>
                    <a href="register" class="btn btn-outline-primary flex-fill">Create Account</a>
                </div>

                <p class="text-muted small mb-0 text-center">
                    Forgot your password? <a href="forgot-password" class="text-decoration-none">Reset it here</a>.
                </p>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>