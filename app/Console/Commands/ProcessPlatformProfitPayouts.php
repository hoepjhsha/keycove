<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Shop\PlatformPayoutService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-platform-profit-payouts {--date= : Processing date in Y-m-d format} {--dry-run : Preview eligible payout amount without creating or processing a payout batch} {--no-process : Create or load the payout batch but do not send the payout request}')]
#[Description('Create and process the weekly platform profit payout batch')]
class ProcessPlatformProfitPayouts extends Command
{
    public function handle(PlatformPayoutService $platformPayoutService): int
    {
        $runAt = $this->resolveRunAt();

        if ((bool) $this->option('dry-run')) {
            $preview = $platformPayoutService->preview($runAt);

            $this->info(sprintf(
                'Platform payout preview: period=%s..%s cutoff=%s eligible_items=%d amount=%.2f',
                $preview['period_start']->toDateString(),
                $preview['period_end']->toDateString(),
                $preview['settlement_cutoff_at']->toDateTimeString(),
                $preview['eligible_count'],
                $preview['eligible_amount'],
            ));

            return self::SUCCESS;
        }

        $platformPayout = $platformPayoutService->runWeekly($runAt, ! (bool) $this->option('no-process'));

        if ($platformPayout === null) {
            $this->info('No eligible platform profit payout batch found for this run.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Platform payout %s processed with status=%s amount=%.2f',
            $platformPayout->payout_code,
            $platformPayout->status->name,
            (float) $platformPayout->amount,
        ));

        return self::SUCCESS;
    }

    protected function resolveRunAt(): CarbonImmutable
    {
        $date = $this->option('date');

        return $date !== null
            ? CarbonImmutable::parse((string) $date)
            : CarbonImmutable::now();
    }
}
