<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Services\Import\FileParserFactory;
use App\Services\Import\ImportProcessor;
use App\Services\Import\ImportResult;
use App\Services\Import\ImportValidator;
use App\Services\Import\ValidationError;
use Illuminate\Support\Facades\Log;
use Throwable;

class BulkImportService
{
    public function __construct(
        private FileParserFactory $parserFactory,
        private ImportValidator $validator,
        private ImportProcessor $processor,
        private ProductKeyRepositoryInterface $keyRepository,
        private ProductListingRepositoryInterface $listingRepository
    ) {}

    public function import(string $filePath, string $fileExtension): ImportResult
    {
        try {
            $parser = $this->parserFactory->make($fileExtension);
            $data = $parser->parse($filePath);

            $structureValidation = $this->validator->validateStructure($data);
            if (! $structureValidation->isValid) {
                return new ImportResult(
                    totalRows: count($data),
                    successCount: 0,
                    failureCount: count($data),
                    errors: $structureValidation->errors,
                    hasErrors: true
                );
            }

            $validationErrors = $this->validator->validateRows($data);
            $errorRowNumbers = array_flip(array_map(fn ($e) => $e->rowNumber, $validationErrors));

            $validRows = array_values(array_filter($data, function ($row, $index) use ($errorRowNumbers) {
                $rowNumber = $index + 2;

                return ! isset($errorRowNumbers[$rowNumber]);
            }, ARRAY_FILTER_USE_BOTH));

            $successCount = 0;
            $failureCount = count($validationErrors);

            if (! empty($validRows)) {
                $processResult = $this->processor->process($validRows);
                $successCount = $processResult->successCount;
                $failureCount += $processResult->failureCount;
            }

            return new ImportResult(
                totalRows: count($data),
                successCount: $successCount,
                failureCount: $failureCount,
                errors: $validationErrors,
                hasErrors: $failureCount > 0
            );
        } catch (Throwable $e) {
            Log::error('Bulk import failed', [
                'user_id'   => auth()->id(),
                'file_path' => $filePath,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return new ImportResult(
                totalRows: 0,
                successCount: 0,
                failureCount: 0,
                errors: [new ValidationError(0, 'import', 'Import failed due to an error: '.$e->getMessage(), [])],
                hasErrors: true
            );
        }
    }
}
