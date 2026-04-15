<?php

declare(strict_types=1);

namespace App\Services\Import\Parsers;

use App\Services\Import\Contracts\FileParserInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxParser implements FileParserInterface
{
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $headers = [];
        $result = [];

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            if ($rowIndex === 1) {
                $headers = array_map('strtolower', array_map('trim', $rowData));

                continue;
            }

            if (array_filter($rowData) === []) {
                continue;
            }

            $associatedRow = [];
            foreach ($headers as $index => $header) {
                $value = $rowData[$index] ?? null;
                $associatedRow[$header] = $value === '' ? null : $value;
            }

            $result[] = $associatedRow;
        }

        return $result;
    }

    public function supports(): string
    {
        return 'xlsx';
    }
}
