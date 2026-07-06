<?php
/**
 * public/admin/users.php
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;
use App\Models\User;

if (!Session::get('admin_id')) {
    header('Location: index.php');
    exit;
}

$adminName = Session::get('admin_name');
$activePage = 'users';

$users = (new User())->getAll();

$verifiedCount = 0;
$unverifiedCount = 0;
foreach ($users as $u) {
    if (!empty($u['is_verified'])) {
        $verifiedCount++;
    } else {
        $unverifiedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/favicon.png">
    <title>Admin Users - <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php require __DIR__ . '/../../views/admin-partials/admin-header.php'; ?>

    <div class="dashboard-body">
        <?php require __DIR__ . '/../../views/admin-partials/admin-sidebar.php'; ?>

        <main class="dashboard-content">

            <div class="content-card">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>Registered Users</h5>
                    <span class="badge rounded-pill" style="background:#6a2fb0;">
                        Total: <?= count($users) ?>
                    </span>
                </div>

                <div class="status-filter-tabs mb-3">
                    <button type="button" class="status-filter-btn active" data-filter="all">
                        <i class="bi bi-list-ul me-1"></i>All <span class="count-badge"><?= count($users) ?></span>
                    </button>
                    <button type="button" class="status-filter-btn" data-filter="verified">
                        <i class="bi bi-patch-check-fill me-1"></i>Verified <span
                            class="count-badge"><?= $verifiedCount ?></span>
                    </button>
                    <button type="button" class="status-filter-btn" data-filter="unverified">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Unverified <span
                            class="count-badge"><?= $unverifiedCount ?></span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="usersTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th>Mobile</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr data-status="<?= !empty($u['is_verified']) ? 'verified' : 'unverified' ?>">
                                    <td class="serial-no"></td>
                                    <td class="d-flex align-items-center gap-2">
                                        <?php if (!empty($u['profile_pic'])): ?>
                                            <img src="<?= htmlspecialchars(UPLOAD_URL . $u['profile_pic']) ?>" alt=""
                                                class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                                        <?php else: ?>
                                            <span class="avatar-circle" style="width:32px;height:32px;font-size:13px;">
                                                <?= htmlspecialchars(strtoupper(substr($u['full_name'] ?? 'U', 0, 1))) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($u['full_name'] ?? '') ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($u['plain_password'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($u['mobile'] ?? '') ?></td>
                                    <td>
                                        <?php if (!empty($u['is_verified'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-patch-check-fill me-1"></i>Verified
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Unverified
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= !empty($u['created_at'])
                                            ? htmlspecialchars(date('d M Y, h:i A', strtotime($u['created_at'])))
                                            : '—' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary view-user-btn"
                                            data-user='<?= htmlspecialchars(json_encode($u), ENT_QUOTES, "UTF-8") ?>'
                                            title="View details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View User Modal -->
            <div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content nomination-preview-modal">
                        <div class="modal-header nomination-preview-header">
                            <h5 class="modal-title mb-0"><i class="bi bi-person-fill me-2"></i>User Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="viewUserBody">
                            <!-- Filled dynamically by JS -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <?php require __DIR__ . '/../../views/admin-partials/admin-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables core + Bootstrap 5 styling -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/js/dataTables.bootstrap5.min.js"></script>

    <!-- Buttons extension (CSV / Excel / Print export) — core package only -->
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.3/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            let currentStatusFilter = 'all';

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'usersTable') {
                    return true;
                }
                if (currentStatusFilter === 'all') {
                    return true;
                }
                const rowStatus = $(table.row(dataIndex).node()).data('status');
                return rowStatus === currentStatusFilter;
            });

            const table = $('#usersTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[5, 'desc']], // Registered On column, newest first
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false, className: 'text-center' },
                    { targets: -1, orderable: false, searchable: false } // Actions column
                ],
                dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"Bf>rt<"d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"lip>',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="bi bi-filetype-csv me-1"></i>CSV',
                        className: 'btn btn-sm btn-outline-secondary',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
                        className: 'btn btn-sm btn-outline-secondary',
                        exportOptions: { columns: ':not(:last-child)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer me-1"></i>Print',
                        className: 'btn btn-sm btn-outline-secondary',
                        exportOptions: { columns: ':not(:last-child)' }
                    }
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search users...',
                    paginate: { previous: '‹', next: '›' }
                },
                drawCallback: function () {
                    const api = this.api();
                    const startIndex = api.context[0]._iDisplayStart;
                    api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                        cell.innerHTML = startIndex + i + 1;
                    });
                }
            });

            $('.status-filter-btn').on('click', function () {
                $('.status-filter-btn').removeClass('active');
                $(this).addClass('active');
                currentStatusFilter = $(this).data('filter');
                table.draw();
            });
        });

        // ---- View Details modal ----
        const viewUserModalEl = document.getElementById('viewUserModal');
        const viewUserModal = new bootstrap.Modal(viewUserModalEl);

        document.querySelectorAll('.view-user-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const data = JSON.parse(btn.getAttribute('data-user'));

                const esc = function (v) {
                    const d = document.createElement('div');
                    d.textContent = v || '—';
                    return d.innerHTML;
                };

                document.getElementById('viewUserBody').innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Full Name</span>
                            <span class="preview-value">${esc(data.full_name)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Email</span>
                            <span class="preview-value">${esc(data.email)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Mobile</span>
                            <span class="preview-value">${esc(data.mobile)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Account Status</span>
                            <span class="preview-value">${parseInt(data.is_verified) ? 'Verified' : 'Not Verified'}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Password</span>
                            <span class="preview-value">${esc(data.plain_password)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Registered On</span>
                            <span class="preview-value">${esc(data.created_at)}</span>
                        </div></div>
                    </div>
                `;

                viewUserModal.show();
            });
        });

        // Mobile sidebar toggle
        document.getElementById('adminSidebarToggle')?.addEventListener('click', function () {
            document.getElementById('adminSidebar').classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    </script>
</body>

</html>