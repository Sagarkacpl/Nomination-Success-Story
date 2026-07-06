<?php
/**
 * views/admin-partials/admin-sidebar.php
 * Expects: $activePage (string) in scope from the including page.
 */
$activePage = $activePage ?? '';
?>
<aside class="dashboard-sidebar" id="adminSidebar">
    <nav class="nav flex-column pt-3">
        <a href="dashboard.php" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="nominations.php" class="nav-link <?= $activePage === 'nominations' ? 'active' : '' ?>">
            <i class="bi bi-award-fill"></i> Nominations
        </a>
        <a href="users.php" class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Users
        </a>
        <a href="logout.php" class="nav-link text-danger-subtle">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>