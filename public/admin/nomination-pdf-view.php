<?php
/**
 * public/admin/nomination-pdf-view.php
 * Admin-only — generates PDF for ANY nomination by id (unlike the
 * user-facing nomination-pdf.php which only shows the logged-in
 * user's own nomination).
 */
require __DIR__ . '/../bootstrap.php';

use App\Core\Session;
use App\Models\Nomination;
use App\Models\NominationAchievement;

if (!Session::get('admin_id')) {
    header('Location: index.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    die('Invalid nomination id.');
}

$nomination = (new Nomination())->findById($id);

if (!$nomination || empty($nomination['final_submission'])) {
    http_response_code(404);
    die('Nomination not found or not yet finally submitted.');
}

// ---- Achievements (nomination_achievements table se) ----
$achievements = (new NominationAchievement())->findByNominationId((int) $nomination['id']);

// ---- Engagement codes -> readable labels (same map as user-facing version) ----
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

$savedEngagements = !empty($nomination['engagement'])
    ? explode(',', $nomination['engagement'])
    : [];

$engagementLabels = [];
foreach ($savedEngagements as $code) {
    if ($code === 'other' && !empty($nomination['engagement_other_text'])) {
        $engagementLabels[] = htmlspecialchars($nomination['engagement_other_text'], ENT_QUOTES, 'UTF-8');
        continue;
    }
    $engagementLabels[] = $engagementOptions[$code] ?? ucfirst(str_replace('_', ' ', $code));
}

require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isFontSubsettingEnabled', true);

$dompdf = new Dompdf($options);

ob_start();
require __DIR__ . '/../../resources/pdf/nomination-template.php';
$html = ob_get_clean();

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$safeRegNo = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $nomination['registration_number']);
$filename = 'ICAI_Nomination_' . $safeRegNo . '.pdf';

$dompdf->stream($filename, ['Attachment' => true]);
exit;