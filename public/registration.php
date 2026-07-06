<?php
/**
 * public/registration.php
 * Accessible as /registration — only for logged-in users.
 */
require __DIR__ . '/bootstrap.php';
use App\Core\Session;
use App\Models\Nomination;
if (!Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/');
    exit;
}
$userName = Session::get('user_name');
$activePage = 'registration';
// ---- Pehle se saved data fetch karo (agar user ne kuch bhara hai) ----
$userId = (int) Session::get('user_id');
$nomination = (new Nomination())->findByUserId($userId) ?? [];
$isFinalSubmitted = !empty($nomination['final_submission']);


$savedEngagements = !empty($nomination['engagement'])
    ? explode(',', $nomination['engagement'])
    : [];

$engagementOptionsAll = [
    'practice' => 'Practice',
    'industry' => 'Industry',
    'business' => 'Business',
    'government' => 'Government',
    'psu' => 'PSU',
    'academia' => 'Academia',
    'entrepreneur' => 'Entrepreneur',
    'independent_director' => 'Independent Director',
    'social_sector' => 'Social Sector / NGO',
    'other' => 'Other (Specify)',
];
$previewEngagementLabels = [];
foreach ($savedEngagements as $code) {
    if ($code === 'other' && !empty($nomination['engagement_other_text'])) {
        $previewEngagementLabels[] = $nomination['engagement_other_text'];
        continue;
    }
    $previewEngagementLabels[] = $engagementOptionsAll[$code] ?? ucfirst(str_replace('_', ' ', $code));
}
// Helper: form field ki saved value nikalne ke liye
function val(array $data, string $key): string
{
    return htmlspecialchars($data[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
function disabledAttr(bool $isFinalSubmitted): string
{
    return $isFinalSubmitted ? 'disabled' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <title>Registration - <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php require __DIR__ . '/../views/partials/dashboard-header.php'; ?>
    <div class="dashboard-body">
        <?php require __DIR__ . '/../views/partials/dashboard-sidebar.php'; ?>
        <main class="dashboard-content">
            <div class="content-card mb-3">
                <div class="form-page-header mb-4">
                    <?php if ($isFinalSubmitted): ?>
                        <button type="button" class="btn btn-sm float-end ms-auto text-white"
                            style="background: linear-gradient(135deg, #6a2fb0, #8e1e8e)" data-bs-toggle="modal"
                            data-bs-target="#nominationPreviewModal">
                            <i class="bi bi-eye-fill me-1"></i>Preview Submission
                        </button>
                    <?php endif; ?>
                    <h3 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Nomination Form — Success
                        Story</h3>
                    <?php if ($isFinalSubmitted): ?>
                        <div class="alert alert-info d-flex align-items-center mt-2 mb-0" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Your nomination (Reg.No:&nbsp;<strong>
                                <?= htmlspecialchars($nomination['registration_number'] ?? '') ?></strong>)
                            has already been submitted and can no longer be edited.
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Fields marked <span class="text-danger">*</span> are required.</p>
                    <?php endif; ?>
                </div>
                <form id="nominationForm" novalidate autocomplete="off">
                    <input type="hidden" id="csrf_token"
                        value="<?= htmlspecialchars(\App\Core\Session::csrfToken()) ?>">
                    <!-- SECTION A -->
                    <h5 class="section-title"><span class="sec-badge">A</span> Personal Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">1. Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" required
                                <?= disabledAttr($isFinalSubmitted) ?> placeholder="e.g. Priya Sharma"
                                value="<?= val($nomination, 'full_name') ?>">
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">2. ICAI Membership Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="membership_no" required pattern="[0-9]{6}"
                                maxlength="6" <?= disabledAttr($isFinalSubmitted) ?>
                                placeholder="6-digit number e.g. 123456"
                                value="<?= val($nomination, 'membership_no') ?>">
                            <div class="invalid-feedback">Enter a valid 6-digit membership number.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">3. Current Designation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="designation" required
                                <?= disabledAttr($isFinalSubmitted) ?> placeholder="e.g. Partner, CFO, Director"
                                value="<?= val($nomination, 'designation') ?>">
                            <div class="invalid-feedback">Please enter your current designation.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">4. Organization / Firm Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="organization" required
                                <?= disabledAttr($isFinalSubmitted) ?> placeholder="e.g. ABC & Associates LLP"
                                value="<?= val($nomination, 'organization') ?>">
                            <div class="invalid-feedback">Please enter your organization / firm name.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">5. Nature of Engagement <span
                                    class="text-danger">*</span></label>
                            <div class="form-text mb-2">Select all that apply.</div>
                            <div class="engagement-pills">
                                <?php
                                $engagementOptions = [
                                    'practice' => 'Practice',
                                    'industry' => 'Industry',
                                    'business' => 'Business',
                                    'government' => 'Government',
                                    'psu' => 'PSU',
                                    'academia' => 'Academia',
                                    'entrepreneur' => 'Entrepreneur',
                                    'independent_director' => 'Independent Director',
                                    'social_sector' => 'Social Sector / NGO',
                                    'other' => 'Other (Specify)',
                                ];
                                foreach ($engagementOptions as $val => $label):
                                    $isChecked = in_array($val, $savedEngagements, true);
                                    ?>
                                    <input type="checkbox" class="btn-check" name="engagement[]" id="eng_<?= $val ?>"
                                        value="<?= $val ?>" autocomplete="off" <?= $isChecked ? 'checked' : '' ?>
                                        <?= disabledAttr($isFinalSubmitted) ?>>
                                    <label class="pill-check" for="eng_<?= $val ?>">
                                        <span class="pill-text"><?= $label ?></span>
                                        <span class="pill-tick"><i class="bi bi-check"></i></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="engagement-error text-danger small mt-2" style="display:none !important;">Please
                                select at least one option.</div>
                            <input type="text"
                                class="form-control mt-3 <?= in_array('other', $savedEngagements, true) ? '' : 'd-none' ?>"
                                id="engagement_other_text" name="engagement_other_text"
                                placeholder="Please specify your engagement type"
                                value="<?= val($nomination, 'engagement_other_text') ?>"
                                <?= disabledAttr($isFinalSubmitted) ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">6. LinkedIn Profile URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="linkedin" required
                                placeholder="https://linkedin.com/in/username" pattern="https?://.*linkedin\.com/.*"
                                value="<?= val($nomination, 'linkedin') ?>" <?= disabledAttr($isFinalSubmitted) ?>>
                            <div class="invalid-feedback">Enter a valid LinkedIn profile URL.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">7. Total Professional Experience <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="experience" required
                                placeholder="e.g. 12 years" value="<?= val($nomination, 'experience') ?>"
                                <?= disabledAttr($isFinalSubmitted) ?>>
                            <div class="invalid-feedback">Please enter your total professional experience.</div>
                        </div>
                    </div>
                    <!-- SECTION B -->
                    <h5 class="section-title"><span class="sec-badge">B</span> Contact Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">8. Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required
                                placeholder="e.g. priya.sharma@example.com" value="<?= val($nomination, 'email') ?>"
                                <?= disabledAttr($isFinalSubmitted) ?>>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">9. Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile" required pattern="[6-9][0-9]{9}"
                                maxlength="10" placeholder="10-digit mobile number"
                                value="<?= val($nomination, 'mobile') ?>" <?= disabledAttr($isFinalSubmitted) ?>>
                            <div class="invalid-feedback">Enter a valid 10-digit mobile number.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">10. State <span class="text-danger">*</span></label>
                            <select class="form-select" name="state" id="state" required
                                data-saved-state="<?= val($nomination, 'state') ?>">
                                <option value="" <?= empty($nomination['state']) ? 'selected' : '' ?> disabled>Select
                                    State</option>
                            </select>
                            <div class="invalid-feedback">Please select your state.</div>
                        </div>
                    </div>
                    <!-- SECTION C -->
                    <?php
                    $achievementModel = new \App\Models\NominationAchievement();
                    $savedAchievements = !empty($nomination['id'])
                        ? $achievementModel->findByNominationId((int) $nomination['id'])
                        : [];
                    if (empty($savedAchievements)) {
                        $savedAchievements = [['id' => '', 'achievement_text' => '', 'document_filename' => null]];
                    }
                    ?>

                    <h5 class="section-title"><span class="sec-badge">C</span> Professional Profile</h5>
                    <label class="form-label d-block">11. Major Professional Achievement<span
                            class="text-danger">*</span></label>
                    <div class="col-12 mb-3">
                        <div id="achievementsContainer">
                            <?php foreach ($savedAchievements as $ach): ?>
                                <div class="achievement-row border rounded p-3 mb-3 position-relative"
                                    data-achievement-id="<?= htmlspecialchars((string) $ach['id']) ?>">
                                    <button type="button" class="btn btn-sm btn-danger remove-achievement-btn"
                                        style="position:absolute; top:8px; right:8px; <?= count($savedAchievements) > 1 ? '' : 'display:none;' ?>"
                                        <?= disabledAttr($isFinalSubmitted) ?>>
                                        <i class="bi bi-x-lg"></i>
                                    </button>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Major Professional Achievement <span
                                                    class="text-danger">*</span></label>
                                            <div class="form-text mb-1">Kindly submit your story covering key aspects such
                                                as your profressional journey, challenges overcom, defining moments of
                                                pride, your vision for the future, etc.</div>
                                            <input type="text" class="form-control achievement-text-input" required
                                                minlength="10" placeholder="Describe your key achievement..."
                                                value="<?= htmlspecialchars($ach['achievement_text']) ?>"
                                                <?= disabledAttr($isFinalSubmitted) ?>>
                                            <div class="invalid-feedback">Please describe this achievement (minimum 10
                                                characters).</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Upload Supporting Document</label>
                                            <div class="form-text mb-1">Optional (PDF). Max size: 5 MB.</div>
                                            <input type="file" class="form-control achievement-doc-input" accept=".pdf"
                                                <?= disabledAttr($isFinalSubmitted) ?>>
                                            <?php if (!empty($ach['document_filename'])): ?>
                                                <div class="mb-1 small existing-file-note">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>
                                                    Current file uploaded
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn text-white nomination-pdf-btn" id="addMoreAchievementBtn"
                            <?= disabledAttr($isFinalSubmitted) ?>>
                            <i class="bi bi-plus-lg me-1"></i>Add More
                        </button>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Your Inspiring Success Story <span
                                class="text-danger">*</span></label>
                        <div class="form-text mb-1">Example: "Breaking Barriers through Financial Leadership"</div>
                        <input type="text" class="form-control" name="story_title" required
                            placeholder="e.g. Breaking Barriers through Financial Leadership"
                            value="<?= val($nomination, 'story_title') ?>" <?= disabledAttr($isFinalSubmitted) ?>>
                        <div class="invalid-feedback">Please enter a title for your story.</div>
                    </div>
                    <!-- SECTION E -->
                    <h5 class="section-title"><span class="sec-badge">D</span> Consent &amp; Declaration</h5>
                    <div class="mb-4">
                        <label class="form-label d-block">12. Declaration <span class="text-danger">*</span></label>
                        <div class="declaration-item">
                            <input class="form-check-input" type="checkbox" name="declaration_true" id="decl1" required
                                <?= !empty($nomination['declaration_true']) ? 'checked' : '' ?>
                                <?= disabledAttr($isFinalSubmitted) ?>>
                            <label for="decl1">
                                I hereby declare that all information furnished by me is true and correct to the best of
                                my knowledge and belief.
                            </label>
                        </div>
                        <div class="declaration-item">
                            <input class="form-check-input" type="checkbox" name="declaration_original" id="decl2"
                                required <?= !empty($nomination['declaration_original']) ? 'checked' : '' ?>
                                <?= disabledAttr($isFinalSubmitted) ?>>
                            <label for="decl2">
                                I confirm that the submitted success story is original in nature.
                            </label>
                        </div>
                        <div class="declaration-item">
                            <input class="form-check-input" type="checkbox" name="declaration_no_guarantee" id="decl3"
                                required <?= !empty($nomination['declaration_no_guarantee']) ? 'checked' : '' ?>
                                <?= disabledAttr($isFinalSubmitted) ?>>
                            <label for="decl3">
                                I understand that submission of the story does not guarantee publication.
                            </label>
                        </div>
                        <div class="declaration-error text-danger small mt-2" style="display:none !important;">
                            All three declarations must be accepted.
                        </div>
                    </div>
                    <?php if (!$isFinalSubmitted): ?>
                        <button type="submit" class="btn btn-submit-nomination">
                            <i class="bi bi-send-fill me-2"></i>Submit Nomination
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="bi bi-lock-fill me-2"></i>Submission Locked
                        </button>
                    <?php endif; ?>
                    <div id="formSuccessMsg" class="alert alert-success mt-3 d-none">
                        Form filled correctly! (Backend submission not connected yet.)
                    </div>
                    <div id="formSuccessMsg" class="alert alert-success mt-3 d-none">
                        Form filled correctly! (Backend submission not connected yet.)
                    </div>
                </form>
                <?php if ($isFinalSubmitted): ?>
                    <div class="modal fade" id="nominationPreviewModal" tabindex="-1"
                        aria-labelledby="nominationPreviewLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                            <div class="modal-content nomination-preview-modal">
                                <div class="modal-header nomination-preview-header">
                                    <div>
                                        <h5 class="modal-title mb-0" id="nominationPreviewLabel">
                                            <i class="bi bi-file-earmark-text-fill me-2"></i>Nomination Submission — Preview
                                        </h5>
                                        <div class="preview-regno mt-2">
                                            Registration No: <strong>
                                                <?= htmlspecialchars($nomination['registration_number'] ?? '') ?>
                                            </strong>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h6 class="preview-section-title"><i class="bi bi-person-fill me-2"></i>Personal
                                        Information</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="preview-field">
                                                <span class="preview-label">1. Full Name</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['full_name'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="preview-field">
                                                <span class="preview-label">2. Membership No.</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['membership_no'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="preview-field">
                                                <span class="preview-label">3. Designation</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['designation'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="preview-field">
                                                <span class="preview-label">4. Organization</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['organization'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <span class="preview-label d-block mb-1">5. Nature of Engagement</span>
                                            <?php foreach ($previewEngagementLabels as $label): ?>
                                                <span class="preview-tag">
                                                    <?= htmlspecialchars($label) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="preview-field">
                                                <span class="preview-label">6. LinkedIn</span>
                                                <span class="preview-value text-truncate d-block">
                                                    <?= htmlspecialchars($nomination['linkedin'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="preview-field">
                                                <span class="preview-label">7. Experience</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['experience'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="preview-section-title"><i class="bi bi-telephone-fill me-2"></i>Contact
                                        Details</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="preview-field">
                                                <span class="preview-label">8. Email</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['email'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="preview-field">
                                                <span class="preview-label">9. Mobile</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['mobile'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="preview-field">
                                                <span class="preview-label">10. State</span>
                                                <span class="preview-value">
                                                    <?= htmlspecialchars($nomination['state'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <h6 class="preview-section-title"><i class="bi bi-award-fill me-2"></i>Professional
                                        Achievements</h6>
                                    <div class="preview-box mb-3">
                                        <span class="preview-label">11. Major Professional Achievements</span>
                                        <p class="preview-value">
                                            <?= nl2br(htmlspecialchars($nomination['achievements'] ?? '')) ?>
                                        </p>
                                    </div> -->
                                    <h6 class="preview-section-title"><i class="bi bi-award-fill me-2"></i>Professional
                                        Achievements</h6>
                                    <div class="preview-box mb-3">
                                        <span class="preview-label">11. Major Professional Achievements</span>
                                        <?php
                                        $displayAchievements = array_filter($savedAchievements, fn($a) => !empty($a['achievement_text']));
                                        ?>
                                        <?php if (empty($displayAchievements)): ?>
                                            <p class="preview-value text-muted">No achievements added.</p>
                                        <?php else: ?>
                                            <?php foreach ($displayAchievements as $i => $ach): ?>
                                                <div class="mb-2 <?= $i > 0 ? 'pt-2 border-top' : '' ?>">
                                                    <p class="preview-value mb-1">
                                                        <?= ($i + 1) . '. ' . nl2br(htmlspecialchars($ach['achievement_text'])) ?>
                                                    </p>
                                                    <?php if (!empty($ach['document_filename'])): ?>
                                                        <a href="uploads/nomination_docs/<?= $ach['document_filename']; ?>"
                                                            target="_blank" class="small"> <i
                                                                class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>View supporting
                                                            document</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <h6 class="preview-section-title"><i class="bi bi-stars me-2"></i>Success Story</h6>
                                    <div class="preview-box mb-3">
                                        <span class="preview-label">12. Your Inspiring Success Story</span>
                                        <p class="preview-value">
                                            <?= htmlspecialchars($nomination['story_title'] ?? '') ?>
                                        </p>
                                    </div>
                                    <h6 class="preview-section-title"><i class="bi bi-check2-square me-2"></i>Declaration
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <span class="preview-label">13. Declaration</span>
                                        <li class="preview-decl-item">
                                            <i
                                                class="bi <?= !empty($nomination['declaration_true']) ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                            I hereby declare that all information furnished by me is true and correct.
                                        </li>
                                        <li class="preview-decl-item">
                                            <i
                                                class="bi <?= !empty($nomination['declaration_original']) ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                            The submitted success story is original.
                                        </li>
                                        <li class="preview-decl-item">
                                            <i
                                                class="bi <?= !empty($nomination['declaration_no_guarantee']) ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                            Submission does not guarantee publication.
                                        </li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <a href="nomination-pdf.php" target="_blank" class="btn text-white nomination-pdf-btn">
                                        <i class="bi bi-download me-1"></i>Download PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php require __DIR__ . '/../views/partials/dashboard-footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js?v=<?= filemtime(__DIR__ . '/assets/js/auth.js') ?>"></script>
    <script src="assets/js/state-city-data.js?v=<?= filemtime(__DIR__ . '/assets/js/state-city-data.js') ?>"></script>
    <script>
        window.isFinalSubmitted = <?= $isFinalSubmitted ? 'true' : 'false' ?>;
    </script>
    <script
        src="assets/js/registration-autosave.js?v=<?= filemtime(__DIR__ . '/assets/js/registration-autosave.js') ?>"></script>
    <script
        src="assets/js/registration-docs.js?v=<?= filemtime(__DIR__ . '/assets/js/registration-docs.js') ?>"></script>
</body>

</html>