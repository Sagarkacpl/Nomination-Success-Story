<?php

namespace App\Traits;

/**
 * FlashMessageTrait
 * Reusable "flash" success / error / info messages that survive exactly
 * one redirect (stored in session, cleared once read).
 *
 * Usage inside a Controller:
 *   $this->setFlash('success', 'Registered successfully!');
 *   $this->setFlash('error', 'Invalid email or password.');
 *
 * In a view:
 *   include views/partials/flash.php
 */
trait FlashMessageTrait
{
    protected function setFlash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['flash'][$type][] = $message;
    }

    protected function getFlash(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']); // one-time read

        return $flash;
    }

    protected function hasFlash(): bool
    {
        return !empty($_SESSION['flash']);
    }
}
