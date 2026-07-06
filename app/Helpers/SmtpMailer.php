<?php

namespace App\Helpers;

use Exception;

/**
 * SmtpMailer
 * Minimal, dependency-free SMTP client (talks raw SMTP over a socket).
 * Supports STARTTLS (port 587) and implicit SSL (port 465) + AUTH LOGIN.
 *
 * For a production system you may prefer PHPMailer/Symfony Mailer, but
 * this keeps the auth module dependency-free and easy to audit.
 *
 * Usage:
 *   $mailer = new SmtpMailer();
 *   $mailer->send('user@example.com', 'Verify your account', '<p>Hello</p>');
 */
class SmtpMailer
{
    private string $host;
    private int $port;
    private string $encryption; // tls | ssl
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;

    /** @var resource|null */
    private $socket = null;

    private int $timeout = 15;

    public function __construct()
    {
        $this->host       = SMTP_HOST;
        $this->port       = SMTP_PORT;
        $this->encryption = SMTP_ENCRYPTION;
        $this->username   = SMTP_USERNAME;
        $this->password   = SMTP_PASSWORD;
        $this->fromEmail  = SMTP_FROM_EMAIL;
        $this->fromName   = SMTP_FROM_NAME;
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $toName = null): bool
    {
        try {
            $this->connect();
            $this->authenticate();
            $this->dispatch($to, $subject, $htmlBody, $toName);
            $this->quit();
            return true;
        } catch (Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            $this->closeSocket();
            return false; // caller decides how to handle failure (never expose SMTP errors to end users)
        }
    }

    private function connect(): void
    {
        $remote = ($this->encryption === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port;

        $this->socket = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$this->socket) {
            throw new Exception("Unable to connect to SMTP host: {$errstr} ({$errno})");
        }

        $this->readResponse(220);
        $this->command('EHLO ' . $this->heloDomain(), 250);

        if ($this->encryption === 'tls') {
            $this->command('STARTTLS', 220);

            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('Failed to enable TLS encryption.');
            }

            // Must re-issue EHLO after STARTTLS
            $this->command('EHLO ' . $this->heloDomain(), 250);
        }
    }

    private function authenticate(): void
    {
        $this->command('AUTH LOGIN', 334);
        $this->command(base64_encode($this->username), 334);
        $this->command(base64_encode($this->password), 235);
    }

    private function dispatch(string $to, string $subject, string $htmlBody, ?string $toName): void
    {
        $this->command('MAIL FROM:<' . $this->fromEmail . '>', 250);
        $this->command('RCPT TO:<' . $to . '>', 250);
        $this->command('DATA', 354);

        $headers   = [];
        $headers[] = 'From: ' . $this->encodeHeader($this->fromName) . ' <' . $this->fromEmail . '>';
        $headers[] = 'To: ' . ($toName ? $this->encodeHeader($toName) . ' ' : '') . '<' . $to . '>';
        $headers[] = 'Subject: ' . $this->encodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . uniqid('', true) . '@' . $this->heloDomain() . '>';

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody;

        // Dot-stuffing: lines starting with '.' must be escaped per RFC 5321
        $message = preg_replace('/^\./m', '..', $message);

        $this->write($message . "\r\n.\r\n");
        $this->readResponse(250);
    }

    private function quit(): void
    {
        $this->write('QUIT');
        $this->closeSocket();
    }

    private function command(string $cmd, int $expectedCode): string
    {
        $this->write($cmd);
        return $this->readResponse($expectedCode);
    }

    private function write(string $data): void
    {
        if (!$this->socket) {
            throw new Exception('SMTP socket is not open.');
        }
        fwrite($this->socket, $data . "\r\n");
    }

    private function readResponse(int $expectedCode): string
    {
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            // multi-line responses have a '-' after the code; final line has a space
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception("Unexpected SMTP response (expected {$expectedCode}): {$response}");
        }

        return $response;
    }

    private function heloDomain(): string
    {
        return $_SERVER['SERVER_NAME'] ?? 'localhost';
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function closeSocket(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->socket = null;
    }
}
