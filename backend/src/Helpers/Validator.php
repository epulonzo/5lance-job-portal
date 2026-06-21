<?php

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function required(string $field, string $label = null): self
    {
        $label ??= $field;
        if (!isset($this->data[$field]) || trim((string) $this->data[$field]) === '') {
            $this->errors[$field] = "{$label} is required.";
        }

        return $this;
    }

    public function maxLength(string $field, int $max): self
    {
        if (isset($this->data[$field]) && mb_strlen((string) $this->data[$field]) > $max) {
            $this->errors[$field] = ucfirst($field) . " must not exceed {$max} characters.";
        }

        return $this;
    }

    public function numeric(string $field): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && $this->data[$field] !== null && !is_numeric($this->data[$field])) {
            $this->errors[$field] = ucfirst($field) . ' must be a number.';
        }

        return $this;
    }

    public function min(string $field, float $min): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && (float) $this->data[$field] < $min) {
            $this->errors[$field] = ucfirst($field) . " must be at least {$min}.";
        }

        return $this;
    }

    public function date(string $field): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && $this->data[$field] !== null) {
            $value = (string) $this->data[$field];
            $parsed = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$parsed || $parsed->format('Y-m-d') !== $value) {
                $this->errors[$field] = ucfirst($field) . ' must be a valid date in YYYY-MM-DD format.';
            }
        }

        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = ucfirst($field) . ' must be one of: ' . implode(', ', $allowed) . '.';
        }

        return $this;
    }

    public function email(string $field): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && $this->data[$field] !== null) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = ucfirst($field) . ' must be a valid email address.';
            }
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
