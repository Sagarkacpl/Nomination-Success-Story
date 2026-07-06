<?php

namespace App\Helpers;

/**
 * Validator
 * Small fluent-ish validator for form input. Collects errors instead of
 * throwing, so a controller can pass all of them back to the view at once.
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, int $length, string $label): self
    {
        $value = (string)($this->data[$field] ?? '');
        if ($value !== '' && mb_strlen($value) < $length) {
            $this->errors[$field] = "{$label} must be at least {$length} characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $length, string $label): self
    {
        $value = (string)($this->data[$field] ?? '');
        if (mb_strlen($value) > $length) {
            $this->errors[$field] = "{$label} must not exceed {$length} characters.";
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $label): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$otherField] ?? null)) {
            $this->errors[$field] = "{$label} does not match.";
        }
        return $this;
    }

    public function mobile(string $field, string $label = 'Mobile number'): self
    {
        $value = (string)($this->data[$field] ?? '');
        if ($value !== '' && !preg_match('/^[6-9]\d{9}$/', $value)) {
            $this->errors[$field] = "{$label} must be a valid 10-digit Indian mobile number.";
        }
        return $this;
    }

    /**
     * Strong-ish password policy: min length enforced separately via minLength().
     * This adds a check for at least one letter and one number.
     */
    public function strongPassword(string $field, string $label = 'Password'): self
    {
        $value = (string)($this->data[$field] ?? '');
        if ($value !== '' && !preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $value)) {
            $this->errors[$field] = "{$label} must contain at least one letter and one number.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
