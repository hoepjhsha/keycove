<?php

declare(strict_types=1);

namespace App\Services\Import\Contracts;

interface FileParserInterface
{
    /**
     * Parse file into structured data.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $filePath): array;

    /**
     * Get supported file extension.
     */
    public function supports(): string;
}
