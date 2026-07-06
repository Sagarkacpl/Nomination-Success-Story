<?php
// $activePage variable set karke bhejna, taaki current nav item highlight ho (e.g. 'dashboard', 'profile')
$activePage = $activePage ?? '';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'href' => 'dashboard'],
    // ['key' => 'profile', 'label' => 'Profile', 'icon' => 'bi-person-circle', 'href' => 'profile'],
    ['key' => 'registration', 'label' => 'Registration', 'icon' => 'bi-person-plus', 'href' => 'registration'],
    // ['key' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear', 'href' => 'settings'],
];
?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
    <ul class="nav flex-column mt-3">
        <?php foreach ($navItems as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= $activePage === $item['key'] ? 'active' : '' ?>" href="<?= $item['href'] ?>">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="nav-item mt-2">
            <a class="nav-link text-danger" href="logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>