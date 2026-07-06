<?php
/**
 * public/nomination-pdf.php
 *
 * Standalone endpoint — koi router change nahi chahiye.
 * Accessible as /nomination-pdf.php — sirf logged-in aur final-submitted
 * users ke liye. Dompdf se PDF generate karke download karwata hai.
 *
 * PORTABILITY NOTE: Dompdf pure-PHP hai, koi shell_exec / external binary
 * nahi chahiye — isliye shared hosting, VPS, dedicated, Windows, Linux,
 * cloud (AWS/GCP/Azure) sab jagah identical chalega.
 */

require __DIR__ . '/bootstrap.php';

use App\Core\Session;
use App\Models\Nomination;

if (!Session::isLoggedIn()) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$userId     = (int) Session::get('user_id');
$nomination = (new Nomination())->findByUserId($userId);

// ---- Guard: sirf final-submitted nomination ka hi PDF bane ----
if (!$nomination || empty($nomination['final_submission'])) {
    http_response_code(403);
    die('Your nomination has not been finally submitted yet. PDF download is available only after final submission.');
}

// ---- Engagement codes ko readable labels mein convert karo ----
$engagementOptions = [
    'practice'              => 'Practice',
    'industry'               => 'Industry',
    'business'               => 'Business',
    'government'             => 'Government',
    'psu'                    => 'PSU',
    'academia'               => 'Academia',
    'entrepreneur'           => 'Entrepreneur',
    'independent_director'   => 'Independent Director',
    'social_sector'          => 'Social Sector / NGO',
    'other'                  => 'Other (Specify)',
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

$achievementModel = new \App\Models\NominationAchievement();
$achievements = $achievementModel->findByNominationId((int) $nomination['id']);

// ---- Dompdf load ----
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);   // external URLs load nahi karne — fast + safe
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isFontSubsettingEnabled', true);

$dompdf = new Dompdf($options);

// ---- Template render karke HTML string banao ----
ob_start();
require __DIR__ . '/../resources/pdf/nomination-template.php';
$html = ob_get_clean();

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ---- Safe filename (registration number se special chars strip) ----
$safeRegNo = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $nomination['registration_number']);
$filename  = 'ICAI_Nomination_' . $safeRegNo . '.pdf';

// ---- Download force karo ----
$dompdf->stream($filename, ['Attachment' => true]);
exit;