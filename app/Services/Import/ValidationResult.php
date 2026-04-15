<?php

declare(strict_types=1);

namespace App\Services\Import;

class ValidationResult
{
    /**
     * @param  array<ValidationError>  $errors
     */
    public function __construct(
        public bool $isValid,
        public array $errors = []
    ) {}

    public static function valid(): self
    {
        return new self(true);
    }

    /**
     * @param  array<ValidationError>  $errors
     */
    public static function invalid(array $errors): self
    {
        return new self(false, $errors);
    }
}
