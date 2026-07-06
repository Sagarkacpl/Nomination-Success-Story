<nav class="navbar navbar-expand-lg dashboard-topbar sticky-top px-3">
    <button class="btn btn-link text-white d-lg-none me-2" id="sidebarToggle" type="button">
        <i class="bi bi-list fs-3"></i>
    </button>

    <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="dashboard">
        <img src="assets/images/icai-75-logo.jpg" alt="Logo" height="34">
        <span class="d-none d-sm-inline"><?= htmlspecialchars(APP_NAME) ?></span>
    </a>

    <div class="ms-auto dropdown">
        <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2"
            type="button" data-bs-toggle="dropdown">
            <span class="avatar-circle"><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></span>
            <span class="d-none d-md-inline"><?= htmlspecialchars($userName ?? '') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="change-password"><i class="bi bi-person me-2"></i>Change Password</a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </li>
        </ul>
    </div>
</nav>