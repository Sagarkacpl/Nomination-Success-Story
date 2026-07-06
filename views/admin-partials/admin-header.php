<?php
/**
 * views/admin-partials/admin-header.php
 * Expects: $adminName (string) in scope from the including page.
 */
?>
<header class="dashboard-topbar d-flex align-items-center justify-content-between px-3">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm text-white d-lg-none" id="adminSidebarToggle" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>
        <img src="../assets/images/icai-75-logo.jpg" alt="ICAI" height="32" class="d-none d-sm-block">
        <span class="text-white fw-semibold">Admin Panel — Nomination Form Success Story</span>
    </div>

    <div class="dropdown">
        <button class="btn btn-sm d-flex align-items-center gap-2 text-white" type="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <span class="avatar-circle">
                <?= htmlspecialchars(strtoupper(substr($adminName ?? 'A', 0, 1))) ?>
            </span>
            <span class="d-none d-sm-inline"><?= htmlspecialchars($adminName ?? 'Admin') ?></span>
            <i class="bi bi-chevron-down small"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </div>
</header>