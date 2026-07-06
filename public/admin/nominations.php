<?php
/**
 * public/admin/nominations.php
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;
use App\Models\Nomination;
use App\Models\NominationAchievement;

if (!Session::get('admin_id')) {
    header('Location: index.php');
    exit;
}

$adminName = Session::get('admin_name');
$activePage = 'nominations';

$nominations = (new Nomination())->getAll();
$achievementModel = new NominationAchievement();

// ---- Har nomination ke saath uski achievements list bhi attach kar do ----
foreach ($nominations as &$n) {
    $n['achievements_list'] = $achievementModel->findByNominationId((int) $n['id']);
}
unset($n);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/favicon.png">
    <title>Admin Nominations - <?= htmlspecialchars(APP_NAME) ?></title>
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
                    <h5 class="fw-bold mb-0"><i class="bi bi-award-fill me-2"></i>All Nominations</h5>
                    <span class="badge rounded-pill" style="background:#6a2fb0;">
                        Total: <?= count($nominations) ?>
                    </span>
                </div>

                <?php
                $submittedCount = 0;
                $pendingCount = 0;
                foreach ($nominations as $n) {
                    if (!empty($n['final_submission'])) {
                        $submittedCount++;
                    } else {
                        $pendingCount++;
                    }
                }
                ?>

                <div class="status-filter-tabs mb-3">
                    <button type="button" class="status-filter-btn active" data-filter="all">
                        <i class="bi bi-list-ul me-1"></i>All <span
                            class="count-badge"><?= count($nominations) ?></span>
                    </button>
                    <button type="button" class="status-filter-btn" data-filter="submitted">
                        <i class="bi bi-check-circle-fill me-1"></i>Submitted <span
                            class="count-badge"><?= $submittedCount ?></span>
                    </button>
                    <button type="button" class="status-filter-btn" data-filter="draft">
                        <i class="bi bi-hourglass-split me-1"></i>Pending <span
                            class="count-badge"><?= $pendingCount ?></span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="nominationsTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Reg. No.</th>
                                <th>Full Name</th>
                                <th>Membership No.</th>
                                <th>Organization</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>State</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nominations as $n): ?>
                                <tr data-status="<?= !empty($n['final_submission']) ? 'submitted' : 'draft' ?>">
                                    <td class="serial-no"></td>
                                    <td><?= htmlspecialchars($n['registration_number'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($n['full_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($n['membership_no'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($n['organization'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($n['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($n['mobile'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($n['state'] ?? '') ?></td>
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
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary view-nomination-btn"
                                            data-nomination='<?= htmlspecialchars(json_encode($n), ENT_QUOTES, "UTF-8") ?>'
                                            title="View details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (!empty($n['final_submission'])): ?>
                                            <a href="nomination-pdf-view.php?id=<?= (int) $n['id'] ?>" target="_blank"
                                                class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View Nomination Modal -->
            <div class="modal fade" id="viewNominationModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content nomination-preview-modal">
                        <div class="modal-header nomination-preview-header">
                            <div>
                                <h5 class="modal-title mb-0"><i class="bi bi-file-earmark-text-fill me-2"></i>Nomination
                                    Details</h5>
                                <div class="preview-regno mt-2">
                                    Registration No: <strong id="viewRegNo">—</strong>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="viewNominationBody">
                            <!-- Filled dynamically by JS -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Close</button>
                            <a href="#" id="viewNominationPdfBtn" target="_blank"
                                class="btn text-white nomination-pdf-btn d-none">
                                <i class="bi bi-download me-1"></i>Download PDF
                            </a>
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

    <!-- Buttons extension (CSV / Excel / Print export) -->
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.3/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            // ---- Custom filter: matches selected status tab against row's data-status attribute ----
            let currentStatusFilter = 'all';

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
                if (settings.nTable.id !== 'nominationsTable') {
                    return true;
                }
                if (currentStatusFilter === 'all') {
                    return true;
                }
                const rowStatus = $(table.row(dataIndex).node()).data('status');
                return rowStatus === currentStatusFilter;
            });

            const table = $('#nominationsTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[9, 'desc']],
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false, className: 'text-center' }
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
                    searchPlaceholder: 'Search nominations...',
                    paginate: { previous: '‹', next: '›' }
                },
                drawCallback: function (settings) {
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
        const viewModalEl = document.getElementById('viewNominationModal');
        const viewModal = new bootstrap.Modal(viewModalEl);

        document.querySelectorAll('.view-nomination-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const data = JSON.parse(btn.getAttribute('data-nomination'));

                document.getElementById('viewRegNo').textContent = data.registration_number || '—';

                const isSubmitted = !!parseInt(data.final_submission || 0);
                const pdfBtn = document.getElementById('viewNominationPdfBtn');
                if (isSubmitted) {
                    pdfBtn.href = 'nomination-pdf-view.php?id=' + data.id;
                    pdfBtn.classList.remove('d-none');
                } else {
                    pdfBtn.classList.add('d-none');
                }

                const esc = function (v) {
                    const d = document.createElement('div');
                    d.textContent = v || '';
                    return d.innerHTML;
                };

                // ---- Achievements list (naye table se, JSON array data.achievements_list) ----
                const achievementsList = Array.isArray(data.achievements_list) ? data.achievements_list : [];
                const achievementsHtml = achievementsList.length > 0
                    ? achievementsList.map(function (ach, i) {
                        const docLink = ach.document_filename
                            ? `<a href="../../uploads/nomination_docs/${encodeURIComponent(ach.document_filename)}" target="_blank" class="small">
                                 <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>View supporting document
                               </a>`
                            : '';
                        return `
                            <div class="mb-2 ${i > 0 ? 'pt-2 border-top' : ''}">
                                <p class="preview-value mb-1">${i + 1}. ${esc(ach.achievement_text).replace(/\n/g, '<br>')}</p>
                                ${docLink}
                            </div>
                        `;
                    }).join('')
                    : '<p class="text-muted mb-0">No achievements added.</p>';

                document.getElementById('viewNominationBody').innerHTML = `
                    <h6 class="preview-section-title"><i class="bi bi-person-fill me-2"></i>Personal Information</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Full Name</span>
                            <span class="preview-value">${esc(data.full_name)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Membership No.</span>
                            <span class="preview-value">${esc(data.membership_no)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Designation</span>
                            <span class="preview-value">${esc(data.designation)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Organization</span>
                            <span class="preview-value">${esc(data.organization)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">LinkedIn</span>
                            <span class="preview-value text-truncate d-block">${esc(data.linkedin)}</span>
                        </div></div>
                        <div class="col-md-6"><div class="preview-field">
                            <span class="preview-label">Experience</span>
                            <span class="preview-value">${esc(data.experience)}</span>
                        </div></div>
                    </div>

                    <h6 class="preview-section-title"><i class="bi bi-telephone-fill me-2"></i>Contact Details</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><div class="preview-field">
                            <span class="preview-label">Email</span>
                            <span class="preview-value">${esc(data.email)}</span>
                        </div></div>
                        <div class="col-md-4"><div class="preview-field">
                            <span class="preview-label">Mobile</span>
                            <span class="preview-value">${esc(data.mobile)}</span>
                        </div></div>
                        <div class="col-md-4"><div class="preview-field">
                            <span class="preview-label">State</span>
                            <span class="preview-value">${esc(data.state)}</span>
                        </div></div>
                    </div>

                    <h6 class="preview-section-title"><i class="bi bi-award-fill me-2"></i>Professional Achievements</h6>
                    <div class="preview-box mb-3">${achievementsHtml}</div>

                    <h6 class="preview-section-title"><i class="bi bi-stars me-2"></i>Success Story</h6>
                    <div class="preview-box mb-0"><strong>${esc(data.story_title)}</strong></div>
                `;

                viewModal.show();
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