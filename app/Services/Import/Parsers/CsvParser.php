<?php

declare(strict_types=1);

namespace App\Services\Import\Parsers;

use App\Services\Import\Contracts\FileParserInterface;
use League\Csv\Reader;

class CsvParser implements FileParserInterface
{
    public function parse(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        $result = [];
        foreach ($records as $record) {
            $result[] = array_map(function ($value) {
                return $value === '' ? null : $value;
            }, $record);
        }

        return $result;
    }

    public function supports(): string
    {
        return 'csv';
    }
}
