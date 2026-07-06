<?php
/**
 * public/change-password-action.php
 *
 * Standalone endpoint — no router changes needed.
 * Handles: CSRF check, current-password verification, rate limiting,
 * strength validation, reuse-of-recent-passwords check, and the actual
 * update (with history archiving) via User::changePasswordWithHistory().
 */

header('Content-Type: application/json');

require __DIR__ . '/bootstrap.php';

use App\Core\Session;
use App\Models\User;

const PASSWORD_HISTORY_LIMIT = 5;      // how many previous passwords are blocked from reuse
const CHANGE_PWD_MAX_ATTEMPTS = 5;     // wrong "current password" attempts before short lockout

if (!Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!Session::verifyCsrf($csrfToken)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh the page and try again.']);
    exit;
}

$userId = (int) Session::get('user_id');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$userModel = new User();
$user = $userModel->findById($userId);

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// ---- Simple session-based rate limit on wrong current-password attempts ----
$attemptsKey = 'pwd_change_attempts';
$attempts = (int) (Session::get($attemptsKey) ?? 0);

if ($attempts >= CHANGE_PWD_MAX_ATTEMPTS) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many incorrect attempts. Please try again later.']);
    exit;
}

// ---- Verify current password ----
if (!password_verify($currentPassword, $user['password_hash'])) {
    Session::set($attemptsKey, $attempts + 1);
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
}
Session::set($attemptsKey, 0); // reset counter once current password is confirmed correct

// ---- Basic validation ----
$errors = [];

if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
    $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
}
if (
    !preg_match('/[A-Z]/', $newPassword)
    || !preg_match('/[a-z]/', $newPassword)
    || !preg_match('/[0-9]/', $newPassword)
    || !preg_match('/[\W_]/', $newPassword)
) {
    $errors[] = 'Password must include an uppercase letter, a lowercase letter, a number, and a special character.';
}
if ($newPassword !== $confirmPassword) {
    $errors[] = 'New password and confirm password do not match.';
}
if (hash_equals($currentPassword, $newPassword)) {
    $errors[] = 'New password must be different from your current password.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ---- Prevent reuse of recent passwords ----
if ($userModel->isPasswordReused($userId, $newPassword, PASSWORD_HISTORY_LIMIT)) {
    echo json_encode([
        'success' => false,
        'message' => 'You cannot reuse one of your last ' . PASSWORD_HISTORY_LIMIT . ' passwords. Please choose a different one.',
    ]);
    exit;
}

// ---- All checks passed: update ----
try {
    $userModel->changePasswordWithHistory($userId, $newPassword, PASSWORD_HISTORY_LIMIT);
    echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
} catch (\Exception $e) {
    error_log('Change password error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not update password. Please try again.']);
}