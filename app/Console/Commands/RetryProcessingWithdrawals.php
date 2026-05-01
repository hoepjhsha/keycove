<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WithdrawStatus;
use App\Jobs\ProcessSellerWithdrawal;
use App\Models\Withdraw;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:retry-processing-withdrawals {--minutes=5 : Minimum age in minutes before retrying a processing withdrawal}')]
#[Description('Re-dispatch stuck processing withdrawals for asynchronous payout handling')]
class RetryProcessingWithdrawals extends Command
{
    public function handle(): int
    {
        $thresholdMinutes = max(1, (int) $this->option('minutes'));
        $threshold = now()->subMinutes($thresholdMinutes);
        $redispatchedCount = 0;

        Withdraw::query()
            ->where('status', WithdrawStatus::Processing)
            ->where('updated_at', '<=', $threshold)
            ->orderBy('id')
            ->chunkById(100, function ($withdrawals) use (&$redispatchedCount): void {
                foreach ($withdrawals as $withdrawal) {
                    ProcessSellerWithdrawal::dispatch($withdrawal->id);
                    $redispatchedCount++;
                }
            });

        $this->info(sprintf('Redispatched %d processing withdrawal(s).', $redispatchedCount));

        return self::SUCCESS;
    }
}
