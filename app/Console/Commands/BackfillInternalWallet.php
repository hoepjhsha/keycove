<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ComplaintStatus;
use App\Enums\EscrowStatus;
use App\Enums\PaymentStatus;
use App\Enums\WithdrawStatus;
use App\Models\Complaint;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\Withdraw;
use App\Services\InternalWalletService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-internal-wallet')]
#[Description('Backfill internal wallet ledger entries from historical platform money movements')]
class BackfillInternalWallet extends Command
{
    public function handle(InternalWalletService $internalWalletService): int
    {
        $internalWalletService->wallet();

        $paymentCount = 0;
        $escrowHeldCount = 0;
        $escrowReleasedCount = 0;
        $refundCount = 0;
        $withdrawalCount = 0;

        Order::query()
            ->with(['paymentTransactions' => fn ($query) => $query->orderByDesc('id')])
            ->where('payment_status', PaymentStatus::Completed)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($internalWalletService, &$paymentCount): void {
                foreach ($orders as $order) {
                    $paymentTransaction = $order->paymentTransactions
                        ->firstWhere('status', PaymentStatus::Completed)
                        ?? $order->paymentTransactions->first();

                    if ($paymentTransaction === null) {
                        continue;
                    }

                    $internalWalletService->paymentReceived($order, $paymentTransaction, $paymentTransaction->paid_at ?? $order->updated_at ?? now());
                    $paymentCount++;
                }
            });

        Escrow::query()
            ->with('orderItem')
            ->whereIn('status', [
                EscrowStatus::Holding,
                EscrowStatus::Released,
                EscrowStatus::Refunded,
                EscrowStatus::Frozen,
            ])
            ->orderBy('id')
            ->chunkById(100, function ($escrows) use ($internalWalletService, &$escrowHeldCount, &$escrowReleasedCount): void {
                foreach ($escrows as $escrow) {
                    $internalWalletService->escrowHeld($escrow, $escrow->created_at ?? now(), [
                        'source' => 'backfill',
                    ]);
                    $escrowHeldCount++;

                    if ($escrow->status === EscrowStatus::Released) {
                        $internalWalletService->escrowReleased($escrow, $escrow->updated_at ?? now(), [
                            'source' => 'backfill',
                        ]);
                        $escrowReleasedCount++;
                    }
                }
            });

        Complaint::query()
            ->with('orderItem')
            ->where('status', ComplaintStatus::ApprovedRefund)
            ->orderBy('id')
            ->chunkById(100, function ($complaints) use ($internalWalletService, &$refundCount): void {
                foreach ($complaints as $complaint) {
                    $internalWalletService->refundPaid($complaint, $complaint->resolved_at ?? $complaint->updated_at ?? now());
                    $refundCount++;
                }
            });

        Withdraw::query()
            ->orderBy('id')
            ->chunkById(100, function ($withdrawals) use ($internalWalletService, &$withdrawalCount): void {
                foreach ($withdrawals as $withdraw) {
                    $requestStatus = match ($withdraw->status) {
                        WithdrawStatus::Completed,
                        WithdrawStatus::Rejected,
                        WithdrawStatus::Cancelled,
                        WithdrawStatus::Failed => null,
                        default                => null,
                    };

                    $internalWalletService->sellerPayoutRequested($withdraw, $requestStatus);
                    $withdrawalCount++;

                    if ($withdraw->status === WithdrawStatus::Completed) {
                        $internalWalletService->sellerPayoutCompleted($withdraw, $withdraw->processed_at ?? $withdraw->updated_at ?? now(), [
                            'source' => 'backfill',
                        ]);

                        continue;
                    }

                    if (in_array($withdraw->status, [WithdrawStatus::Rejected, WithdrawStatus::Cancelled, WithdrawStatus::Failed], true)) {
                        $internalWalletService->sellerPayoutFailed($withdraw, $withdraw->processed_at ?? $withdraw->updated_at ?? now(), [
                            'source' => 'backfill',
                        ]);
                    }
                }
            });

        $this->info(sprintf(
            'Backfilled internal wallet entries. payments=%d escrow_holds=%d escrow_releases=%d refunds=%d withdrawals=%d',
            $paymentCount,
            $escrowHeldCount,
            $escrowReleasedCount,
            $refundCount,
            $withdrawalCount,
        ));

        return self::SUCCESS;
    }
}
