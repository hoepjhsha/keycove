<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Enums\ProductKeyStatus;

class ImportValidator
{
    private const REQUIRED_COLUMNS = ['listing_slug', 'key_code'];

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
        $existingListingSlugs = $this->getExistingListingSlugs($data);
        $existingKeyCodes = $this->getExistingKeyCodes($data);

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2;

            $listingSlug = trim((string) ($row['listing_slug'] ?? ''));
            if ($listingSlug === '') {
                $errors[] = new ValidationError($rowNumber, 'listing_slug', 'Listing slug is required', $row);

                continue;
            }

            if (! in_array($listingSlug, $existingListingSlugs, true)) {
                $errors[] = new ValidationError($rowNumber, 'listing_slug', "Listing slug '{$listingSlug}' does not exist", $row);

                continue;
            }

            $keyCode = $row['key_code'] ?? '';
            if (empty(trim($keyCode))) {
                $errors[] = new ValidationError($rowNumber, 'key_code', 'Key code cannot be empty', $row);

                continue;
            }

            if (in_array(hash('sha256', (string) $row['key_code']), $existingKeyCodes, true)) {
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
    private function getExistingListingSlugs(array $data): array
    {
        $listingSlugs = array_values(array_filter(array_unique(array_map(
            fn (mixed $listingSlug): string => trim((string) $listingSlug),
            array_column($data, 'listing_slug')
        ))));

        return $this->listingRepository->getModel()
            ->whereIn('slug', $listingSlugs)
            ->pluck('slug')
            ->toArray();
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<string>
     */
    private function getExistingKeyCodes(array $data): array
    {
        $keyHashes = array_map(
            fn (mixed $keyCode): string => hash('sha256', (string) $keyCode),
            array_filter(array_unique(array_column($data, 'key_code')))
        );
        $listingSlugs = array_values(array_filter(array_unique(array_map(
            fn (mixed $listingSlug): string => trim((string) $listingSlug),
            array_column($data, 'listing_slug')
        ))));

        $listingIds = $this->listingRepository->getModel()
            ->whereIn('slug', $listingSlugs)
            ->pluck('id')
            ->toArray();

        return $this->keyRepository->getModel()
            ->whereIn('listing_id', $listingIds)
            ->whereIn('key_hash', $keyHashes)
            ->pluck('key_hash')
            ->toArray();
    }
}
