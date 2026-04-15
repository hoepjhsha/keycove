<?php

declare(strict_types=1);

namespace App\Services\Import;

class ValidationError
{
    public function __construct(
        public int $rowNumber,
        public string $field,
        public string $message,
        public array $rowData
    ) {}
}
