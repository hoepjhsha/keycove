<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use Illuminate\Support\Facades\DB;

class ImportProcessor
{
    /**
     * @param  array<int, array<string, mixed>>  $validRows
     */
    public function process(array $validRows): ProcessResult
    {
        $successCount = 0;
        $failureCount = 0;

        try {
            DB::transaction(function () use ($validRows, &$successCount) {
                foreach ($validRows as $row) {
                    ProductKey::create([
                        'listing_id' => (int) $row['listing_id'],
                        'key_code' => $row['key_code'],
                        'status' => $this->parseStatus($row['status'] ?? null),
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
