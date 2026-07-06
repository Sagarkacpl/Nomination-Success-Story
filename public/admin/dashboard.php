<?php
/**
 * public/admin/dashboard.php
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;
use App\Models\Nomination;
use App\Models\User;

if (!Session::get('admin_id')) {
    header('Location: index.php');
    exit;
}

$adminName = Session::get('admin_name');
$activePage = 'dashboard';

// ---- Fetch data for stats ----
$nominations = (new Nomination())->getAll();
$users = (new User())->getAll();

$totalNominations = count($nominations);
$submittedCount = 0;
$pendingCount = 0;
foreach ($nominations as $n) {
    if (!empty($n['final_submission'])) {
        $submittedCount++;
    } else {
        $pendingCount++;
    }
}

$totalUsers = count($users);

// ---- Recent activity: latest 5 nominations (by created_at) ----
$recentNominations = array_slice($nominations, 0, 5); // already ordered DESC by getAll()
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/favicon.png">
    <title>Admin Dashboard - <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php require __DIR__ . '/../../views/admin-partials/admin-header.php'; ?>

    <div class="dashboard-body">
        <?php require __DIR__ . '/../../views/admin-partials/admin-sidebar.php'; ?>

        <main class="dashboard-content">

            <div class="welcome-banner mb-4">
                <div class="welcome-banner-text">
                    <span class="banner-pill mb-2 d-inline-block">Admin Panel</span>
                    <h3 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($adminName) ?> 👋</h3>
                    <p class="mb-0 text-white-50">Manage nominations, users, and settings from here.</p>
                </div>
                <div class="welcome-banner-deco d-none d-md-flex">
                    <i class="bi bi-shield-check deco-person"></i>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <a href="nominations.php" class="text-decoration-none">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-purple"><i class="bi bi-award-fill"></i></div>
                            <div>
                                <div class="text-muted small">Total Nominations</div>
                                <div class="fw-bold fs-5 text-dark"><?= $totalNominations ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="nominations.php" class="text-decoration-none">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-green"><i class="bi bi-check-circle-fill"></i></div>
                            <div>
                                <div class="text-muted small">Final Submitted</div>
                                <div class="fw-bold fs-5 text-dark"><?= $submittedCount ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="users.php" class="text-decoration-none">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-blue"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="text-muted small">Registered Users</div>
                                <div class="fw-bold fs-5 text-dark"><?= $totalUsers ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="nominations.php" class="text-decoration-none">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon-box bg-amber"><i class="bi bi-hourglass-split"></i></div>
                            <div>
                                <div class="text-muted small">Pending / Draft</div>
                                <div class="fw-bold fs-5 text-dark"><?= $pendingCount ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="content-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent Nominations</h5>
                    <a href="nominations.php" class="small fw-semibold text-decoration-none" style="color:#8e1e8e;">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <?php if (empty($recentNominations)): ?>
                    <p class="text-muted mb-0">No nominations yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Reg. No.</th>
                                    <th>Full Name</th>
                                    <th>Organization</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentNominations as $n): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($n['registration_number'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($n['full_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($n['organization'] ?? '') ?></td>
                                        <td>
                                            <?php if (!empty($n['final_submission'])): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Submitted
                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                    <i class="bi bi-hourglass-split me-1"></i>Draft
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= !empty($n['created_at'])
                                                ? htmlspecialchars(date('d M Y, h:i A', strtotime($n['created_at'])))
                                                : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <?php require __DIR__ . '/../../views/admin-partials/admin-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('adminSidebarToggle')?.addEventListener('click', function () {
            document.getElementById('adminSidebar').classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    </script>
</body>

</html>