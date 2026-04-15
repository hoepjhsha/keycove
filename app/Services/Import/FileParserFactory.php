<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Contracts\FileParserInterface;
use App\Services\Import\Parsers\CsvParser;
use App\Services\Import\Parsers\XlsxParser;
use InvalidArgumentException;

class FileParserFactory
{
    public function make(string $extension): FileParserInterface
    {
        return match ($extension) {
            'csv' => new CsvParser,
            'xlsx' => new XlsxParser,
            default => throw new InvalidArgumentException("Unsupported file type: {$extension}"),
        };
    }
}
