<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use Illuminate\Support\Facades\DB;

class ImportProcessor
{
    public function __construct(
        private ProductListingRepositoryInterface $listingRepository
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $validRows
     */
    public function process(array $validRows): ProcessResult
    {
        $successCount = 0;
        $failureCount = 0;
        $listingIdsBySlug = $this->listingRepository->getModel()
            ->whereIn('slug', array_values(array_unique(array_map(
                fn (array $row): string => trim((string) ($row['listing_slug'] ?? '')),
                $validRows
            ))))
            ->pluck('id', 'slug')
            ->all();

        try {
            DB::transaction(function () use ($validRows, &$successCount, $listingIdsBySlug) {
                foreach ($validRows as $row) {
                    $listingSlug = trim((string) $row['listing_slug']);
                    if (! isset($listingIdsBySlug[$listingSlug])) {
                        throw new \RuntimeException("Listing slug {$listingSlug} does not exist");
                    }

                    ProductKey::create([
                        'listing_id' => $listingIdsBySlug[$listingSlug],
                        'key_code'   => $row['key_code'],
                        'key_hash'   => hash('sha256', (string) $row['key_code']),
                        'status'     => $this->parseStatus($row['status'] ?? null),
                    ]);
                    $successCount++;
                }
            });
        } catch (\Throwable $e) {
            $failureCount = count($validRows) - $successCount;
            throw $e;
        }

        return new ProcessResult($successCount, $failureCount);
    }

    private function parseStatus(?string $status): ProductKeyStatus
    {
        if ($status === null || $status === '') {
            return ProductKeyStatus::Available;
        }

        $normalized = ucfirst(strtolower(trim($status)));

        foreach (ProductKeyStatus::cases() as $case) {
            if ($case->name === $normalized) {
                return $case;
            }
        }

        return ProductKeyStatus::Available;
    }
}
