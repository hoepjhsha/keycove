<?php

declare(strict_types=1);

namespace App\Services\Import;

class ProcessResult
{
    public function __construct(
        public int $successCount,
        public int $failureCount
    ) {}
}
