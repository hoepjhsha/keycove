<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Enums\ProductKeyStatus;

class ImportValidator
{
    private const REQUIRED_COLUMNS = ['listing_id', 'key_code'];

    public function __construct(
        private ProductListingRepositoryInterface $listingRepository,
        private ProductKeyRepositoryInterface $keyRepository
    ) {}

    public function validateStructure(array $data): ValidationResult
    {
        if (empty($data)) {
            return ValidationResult::invalid([
                new ValidationError(0, 'file', 'File contains no data rows', []),
            ]);
        }

        $headers = array_keys($data[0]);
        $missingColumns = array_diff(self::REQUIRED_COLUMNS, $headers);

        if (! empty($missingColumns)) {
            return ValidationResult::invalid([
                new ValidationError(0, 'file', 'Missing required columns: '.implode(', ', $missingColumns), []),
            ]);
        }

        return ValidationResult::valid();
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<ValidationError>
     */
    public function validateRows(array $data): array
    {
        $errors = [];
        $existingListingIds = $this->getExistingListingIds($data);
        $existingKeyCodes = $this->getExistingKeyCodes($data);

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2;

            if (! isset($row['listing_id']) || empty($row['listing_id'])) {
                $errors[] = new ValidationError($rowNumber, 'listing_id', 'Listing ID is required', $row);

                continue;
            }

            $listingId = (int) $row['listing_id'];
            if (! in_array($listingId, $existingListingIds, true)) {
                $errors[] = new ValidationError($rowNumber, 'listing_id', "Listing ID {$listingId} does not exist", $row);

                continue;
            }

            $keyCode = $row['key_code'] ?? '';
            if (empty(trim($keyCode))) {
                $errors[] = new ValidationError($rowNumber, 'key_code', 'Key code cannot be empty', $row);

                continue;
            }

            if (in_array($row['key_code'], $existingKeyCodes, true)) {
                $errors[] = new ValidationError($rowNumber, 'key_code', "Key code '{$row['key_code']}' already exists", $row);
            }

            $status = $row['status'] ?? null;
            if ($status !== null && $status !== '') {
                $validStatuses = array_map(fn ($case) => $case->name, ProductKeyStatus::cases());
                if (! in_array(ucfirst(strtolower($status)), $validStatuses, true)) {
                    $errors[] = new ValidationError(
                        $rowNumber,
                        'status',
                        "Invalid status value '{$status}'. Must be one of: ".implode(', ', $validStatuses),
                        $row
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int>
     */
    private function getExistingListingIds(array $data): array
    {
        $listingIds = array_filter(array_unique(array_column($data, 'listing_id')));

        return $this->listingRepository->getModel()
            ->whereIn('id', $listingIds)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<string>
     */
    private function getExistingKeyCodes(array $data): array
    {
        $keyCodes = array_filter(array_unique(array_column($data, 'key_code')));

        return $this->keyRepository->getModel()
            ->whereIn('key_code', $keyCodes)
            ->pluck('key_code')
            ->toArray();
    }
}
