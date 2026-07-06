<?php
/**
 * resources/pdf/nomination-template.php
 *
 * Ye file directly render nahi hoti browser mein — sirf nomination-pdf.php
 * ke andar ob_start() -> require -> ob_get_clean() se HTML string banane
 * ke liye use hoti hai, jo phir Dompdf ko diya jata hai.
 *
 * Expects (already set by caller):
 *   $nomination        - array (DB row)
 *   $engagementLabels  - array of readable engagement strings
 */
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <style>
    @page {
      margin: 30px;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #333;
      background: #fff;
    }

    .header {
      background: #5B2C6F;
      color: #fff;
      text-align: center;
      padding: 18px;
      border-radius: 8px;
    }

    .header h1 {
      margin: 0;
      font-size: 18px;
    }

    .header h3 {
      margin: 6px 0;
      font-size: 14px;
    }

    .header p {
      margin: 2px;
      font-size: 11px;
    }

    .reg {
      margin-top: 10px;
      display: inline-block;
      background: #fff;
      color: #5B2C6F;
      padding: 6px 18px;
      border-radius: 20px;
      font-weight: bold;
    }

    .section {
      margin-top: 22px;
    }

    .title {
      background: #F4ECF7;
      color: #5B2C6F;
      padding: 8px 12px;
      font-size: 13px;
      font-weight: bold;
      border-left: 5px solid #5B2C6F;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    .info {
      table-layout: fixed;
    }

    .info td {
      width: 50%;
      padding: 12px;
      border: 1px solid #ddd;
      vertical-align: top;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    .label {
      font-size: 10px;
      color: #777;
      text-transform: uppercase;
    }

    .value {
      margin-top: 5px;
      font-size: 12px;
      font-weight: bold;
      color: #222;
      word-wrap: break-word;
      overflow-wrap: break-word;
      word-break: break-all;
    }

    .box {
      border: 1px solid #ddd;
      padding: 12px;
      background: #fafafa;
      line-height: 1.7;
      word-wrap: break-word;
      overflow-wrap: break-word;
      word-break: break-word;
    }

    .tag {
      display: inline-block;
      background: #5B2C6F;
      color: white;
      padding: 4px 10px;
      margin: 3px;
      border-radius: 20px;
      font-size: 10px;
    }

    .declaration {
      border: 1px solid #ddd;
      margin-top: 10px;
      /* table-layout: fixed; */
    }

    .declaration td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .check {
      width: 40px;
      text-align: center;
      font-size: 16px;
      vertical-align: middle;
    }

    .footer {
      margin-top: 30px;
      text-align: center;
      font-size: 10px;
      color: #999;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>The Institute of Chartered Accountants of India</h1>
    <h3>Women & Young Members Excellence Committee (WYMEC)</h3>
    <p>3rd CA Women Excellence Awards</p>
    <div class="reg">
      Registration No : <?= htmlspecialchars($nomination['registration_number'] ?? '') ?>
    </div>
  </div>

  <div class="section">
    <div class="title">PERSONAL INFORMATION</div>
    <table class="info">
      <tr>
        <td>
          <div class="label">Full Name</div>
          <div class="value"><?= htmlspecialchars($nomination['full_name'] ?? '') ?></div>
        </td>
        <td>
          <div class="label">Membership No.</div>
          <div class="value"><?= htmlspecialchars($nomination['membership_no'] ?? '') ?></div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="label">Designation</div>
          <div class="value"><?= htmlspecialchars($nomination['designation'] ?? '') ?></div>
        </td>
        <td>
          <div class="label">Organization</div>
          <div class="value"><?= htmlspecialchars($nomination['organization'] ?? '') ?></div>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="label">Nature of Engagement</div>
          <br>
          <?php foreach ($engagementLabels as $label): ?>
            <span class="tag"><?= htmlspecialchars($label) ?></span>
          <?php endforeach; ?>
        </td>
      </tr>
      <tr>
        <td>
          <div class="label">LinkedIn</div>
          <div class="value"><?= htmlspecialchars($nomination['linkedin'] ?? '') ?></div>
        </td>
        <td>
          <div class="label">Experience</div>
          <div class="value"><?= htmlspecialchars($nomination['experience'] ?? '') ?></div>
        </td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="title">CONTACT DETAILS</div>
    <table class="info">
      <tr>
        <td>
          <div class="label">Email</div>
          <div class="value"><?= htmlspecialchars($nomination['email'] ?? '') ?></div>
        </td>
        <td>
          <div class="label">Mobile</div>
          <div class="value"><?= htmlspecialchars($nomination['mobile'] ?? '') ?></div>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="label">State</div>
          <div class="value"><?= htmlspecialchars($nomination['state'] ?? '') ?></div>
        </td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="title">PROFESSIONAL ACHIEVEMENTS</div>
    <?php if (empty($achievements)): ?>
      <div class="box">No achievements added.</div>
    <?php else: ?>
      <?php foreach ($achievements as $i => $ach): ?>
        <div class="box" style="<?= $i > 0 ? 'margin-top:8px;' : '' ?>">
          <strong><?= $i + 1 ?>.</strong> <?= nl2br(htmlspecialchars($ach['achievement_text'])) ?>
          <?php if (!empty($ach['document_filename'])): ?>
            <div style="margin-top:4px; font-size:10px; color:#777;">
              Supporting document attached: <?= htmlspecialchars($ach['document_filename']) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="section">
    <div class="title">SUCCESS STORY</div>
    <div class="box">
      <strong><?= htmlspecialchars($nomination['story_title'] ?? '') ?></strong>
    </div>
  </div>

  <div class="section">
    <div class="title">DECLARATION</div>
    <table class="declaration">
      <tr>
        <td class="check"><?= !empty($nomination['declaration_true']) ? "&#10004;" : "&#10008;" ?></td>
        <td>I hereby declare that all information furnished by me is true and correct.</td>
      </tr>
      <tr>
        <td class="check"><?= !empty($nomination['declaration_original']) ? "&#10004;" : "&#10008;" ?></td>
        <td>The submitted success story is original.</td>
      </tr>
      <tr>
        <td class="check"><?= !empty($nomination['declaration_no_guarantee']) ? "&#10004;" : "&#10008;" ?></td>
        <td>Submission does not guarantee publication.</td>
      </tr>
    </table>
  </div>

  <div class="footer">
    Generated on <?= date('d M Y h:i A') ?><br>
    ICAI • WYMEC Nomination Portal
  </div>
</body>

</html>