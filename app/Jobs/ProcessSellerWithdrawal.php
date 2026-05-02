<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Shop\SellerWithdrawalService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ProcessSellerWithdrawal implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(public int $withdrawId)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'seller-withdrawal:'.$this->withdrawId;
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('seller-withdrawal:'.$this->withdrawId))
                ->releaseAfter(60)
                ->expireAfter(180),
        ];
    }

    public function handle(SellerWithdrawalService $sellerWithdrawalService): void
    {
        $sellerWithdrawalService->processApprovedWithdrawal($this->withdrawId);
    }
}
