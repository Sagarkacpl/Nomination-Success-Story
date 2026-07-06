<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\Validator;
use App\Helpers\FileUploader;
use App\Helpers\SmtpMailer;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ==================== REGISTER ====================

    public function showRegister(): void
    {
        $this->view('auth/register', [
            'csrf_token' => Session::csrfToken(),
        ]);
    }

    public function register(): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrfOrFail();

        $input = [
            'full_name' => $this->sanitize($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'mobile' => trim($_POST['mobile'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
        ];

        $validator = new Validator($input);
        $validator->required('full_name', 'Full name')
            ->maxLength('full_name', 150, 'Full name')
            ->required('email', 'Email')
            ->email('email')
            ->required('mobile', 'Mobile number')
            ->mobile('mobile')
            ->required('password', 'Password')
            ->minLength('password', PASSWORD_MIN_LENGTH, 'Password')
            ->strongPassword('password')
            ->matches('confirm_password', 'password', 'Confirm password');

        if ($validator->fails()) {
            foreach ($validator->errors() as $msg) {
                $this->setFlash('error', $msg);
            }
            $this->redirect('register');
            return;
        }

        if ($this->userModel->emailExists($input['email'])) {
            $this->setFlash('error', 'An account with this email already exists.');
            $this->redirect('register');
            return;
        }

        // Optional profile picture upload
        $profilePicFilename = null;
        if (!empty($_FILES['profile_pic']['name'])) {
            try {
                $uploader = new FileUploader('profile_pics');
                $profilePicFilename = $uploader->upload($_FILES['profile_pic']);
            } catch (Exception $e) {
                $this->setFlash('error', $e->getMessage());
                $this->redirect('register');
                return;
            }
        }

        $verificationToken = bin2hex(random_bytes(32));

        $userId = $this->userModel->create([
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'mobile' => $input['mobile'],
            'password' => $input['password'],
            'profile_pic' => $profilePicFilename,
            'verification_token' => $verificationToken,
        ]);

        $this->sendVerificationEmail($input['email'], $input['full_name'], $verificationToken);

        $this->setFlash('success', 'Registration successful! Please check your email to verify your account.');
        $this->redirect(''); // back to index.php (login page)
    }

    private function sendVerificationEmail(string $email, string $name, string $token): void
    {
        $link = APP_URL . '/verify?token=' . $token;

        $body = "<p>Hi " . htmlspecialchars($name) . ",</p>"
            . "<p>Thanks for registering. Please verify your email by clicking the link below:</p>"
            . "<p><a href=\"{$link}\">{$link}</a></p>"
            . "<p>If you did not create this account, you can ignore this email.</p>";

        $mailer = new SmtpMailer();
        $mailer->send($email, 'Verify your account - ' . APP_NAME, $body, $name);
        // Failure is logged internally by SmtpMailer; we don't block registration on email failure.
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';

        if ($token && $this->userModel->verifyEmailByToken($token)) {
            $this->setFlash('success', 'Email verified successfully. You can now log in.');
        } else {
            $this->setFlash('error', 'Invalid or expired verification link.');
        }

        $this->redirect(''); // back to index.php (login page)
    }

    // ==================== LOGIN ====================
    // index.php IS the login page, so showLogin() is called from public/index.php directly.

    public function showLogin(): void
    {
        $this->view('auth/login', [
            'csrf_token' => Session::csrfToken(),
        ]);
    }

    public function login(): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrfOrFail();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Email and password are required.');
            $this->redirect('');
            return;
        }

        // Brute-force protection: check failed attempts within lockout window
        if ($this->userModel->recentFailedAttempts($email) >= MAX_LOGIN_ATTEMPTS) {
            $this->setFlash('error', 'Too many failed attempts. Please try again after '
                . LOGIN_LOCKOUT_MINUTES . ' minutes.');
            $this->redirect('');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // Same generic error whether user exists or not (avoid user enumeration)
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->userModel->recordLoginAttempt($email, false);
            $this->setFlash('error', 'Invalid email or password.');
            $this->redirect('');
            return;
        }

        if ((int) $user['is_verified'] !== 1) {
            $this->setFlash('error', 'Please verify your email before logging in.');
            $this->redirect('');
            return;
        }

        $this->userModel->recordLoginAttempt($email, true);

        // Prevent session fixation: regenerate ID on privilege change
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['full_name']);

        $this->setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
        $this->redirect('dashboard');
    }

    // ==================== LOGOUT ====================

    public function logout(): void
    {
        Session::destroy();
        $this->redirect(''); // back to index.php (login page)
    }

    // ==================== FORGOT PASSWORD ====================

    public function forgotPassword(): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrfOrFail();

        $email = trim($_POST['email'] ?? '');

        if (!empty($email)) {
            $user = $this->userModel->findByEmail($email);

            // User exist kare ya na kare, hamesha same generic message dikhao (user enumeration se bachne ke liye)
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $this->userModel->setResetToken($user['id'], $token, $expiry);
                $this->sendResetEmail($email, $user['full_name'], $token);
            }
        }

        $this->setFlash('success', 'If an account with that email exists, a password reset link has been sent.');
        $this->redirect('forgot-password');
    }

    private function sendResetEmail(string $email, string $name, string $token): void
    {
        $link = APP_URL . '/reset-password?token=' . $token;

        $body = "<p>Hi " . htmlspecialchars($name) . ",</p>"
            . "<p>We received a request to reset your password. Click the link below to set a new one:</p>"
            . "<p><a href=\"{$link}\">{$link}</a></p>"
            . "<p>This link will expire in 1 hour. If you did not request this, you can safely ignore this email.</p>";

        $mailer = new SmtpMailer();
        $mailer->send($email, 'Reset your password - ' . APP_NAME, $body, $name);
    }

    public function resetPassword(): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrfOrFail();

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            $this->setFlash('error', 'Invalid reset request.');
            $this->redirect('forgot-password');
            return;
        }

        $validUser = $this->userModel->findByValidResetToken($token);

        if (!$validUser) {
            $this->setFlash('error', 'This reset link is invalid or has expired. Please request a new one.');
            $this->redirect('forgot-password');
            return;
        }

        $input = ['password' => $password, 'confirm_password' => $confirmPassword];
        $validator = new Validator($input);
        $validator->required('password', 'Password')
            ->minLength('password', PASSWORD_MIN_LENGTH, 'Password')
            ->strongPassword('password')
            ->matches('confirm_password', 'password', 'Confirm password');

        if ($validator->fails()) {
            foreach ($validator->errors() as $msg) {
                $this->setFlash('error', $msg);
            }
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        $this->userModel->resetPasswordByToken($token, $password);

        $this->setFlash('success', 'Your password has been reset successfully. Please log in with your new password.');
        $this->redirect('index.php');
    }

}
