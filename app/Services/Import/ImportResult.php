<?php

declare(strict_types=1);

namespace App\Services\Import;

class ImportResult
{
    /**
     * @param  array<ValidationError>  $errors
     */
    public function __construct(
        public int $totalRows,
        public int $successCount,
        public int $failureCount,
        public array $errors = [],
        public bool $hasErrors = false
    ) {}

    public function toArray(): array
    {
        return [
            'totalRows' => $this->totalRows,
            'successCount' => $this->successCount,
            'failureCount' => $this->failureCount,
            'errors' => array_map(fn ($error) => [
                'rowNumber' => $error->rowNumber,
                'field' => $error->field,
                'message' => $error->message,
                'rowData' => $error->rowData,
            ], $this->errors),
            'hasErrors' => $this->hasErrors,
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getErrorReport(): array
    {
        return array_map(fn ($error) => [
            'Row' => $error->rowNumber,
            'Field' => $error->field,
            'Error' => $error->message,
            'Listing ID' => $error->rowData['listing_id'] ?? '',
            'Key Code' => $error->rowData['key_code'] ?? '',
            'Status' => $error->rowData['status'] ?? '',
        ], $this->errors);
    }
}
