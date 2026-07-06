<?php
namespace App\Core;
use App\Traits\FlashMessageTrait;
/**
 * Base Controller
 * All controllers extend this to get view rendering, redirects,
 * and flash-message helpers (via FlashMessageTrait).
 */
abstract class Controller
{
    use FlashMessageTrait;
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require $viewFile;
    }
    /**
     * @param string $path e.g. '' (root/login), 'register', 'dashboard'
     */
    protected function redirect(string $path = ''): void
    {
        $url = APP_URL . '/' . ltrim($path, '/');
        header('Location: ' . rtrim($url, '/') . ($path === '' ? '/' : ''));
        exit;
    }
    /**
     * Only allow the given HTTP method, otherwise die with 405.
     */
    protected function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            http_response_code(405);
            exit('Method Not Allowed');
        }
    }
    protected function verifyCsrfOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrf($token)) {
            http_response_code(419);
            exit('Invalid or expired form submission (CSRF check failed). Please go back and try again.');
        }
    }
    /**
     * Basic string sanitizer for output/storage of plain text fields.
     */
    protected function sanitize(?string $value): string
    {
        return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
    }
}
