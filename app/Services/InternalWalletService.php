<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InternalWalletDirection;
use App\Enums\InternalWalletEntryType;
use App\Enums\TransactionStatus;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Models\Complaint;
use App\Models\Escrow;
use App\Models\InternalWalletEntry;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InternalWalletService
{
    public function paymentReceived(Order $order, PaymentTransaction $paymentTransaction, ?Carbon $occurredAt = null): InternalWalletEntry
    {
        return $this->recordEntry(
            type: InternalWalletEntryType::PaymentReceived,
            direction: InternalWalletDirection::Inflow,
            amount: (float) $paymentTransaction->amount,
            status: TransactionStatus::Completed,
            affectsBalance: true,
            occurredAt: $occurredAt ?? $paymentTransaction->paid_at ?? $order->updated_at ?? now(),
            sourceType: PaymentTransaction::class,
            sourceId: $paymentTransaction->id,
            orderId: $order->id,
            idempotencyKey: sprintf('internal-wallet:payment-received:order:%d', $order->id),
            metadata: [
                'gateway'                => $paymentTransaction->gateway?->name,
                'gateway_transaction_id' => $paymentTransaction->gateway_transaction_id,
                'payment_transaction_id' => $paymentTransaction->id,
            ],
        );
    }

    public function escrowHeld(Escrow $escrow, ?Carbon $occurredAt = null, array $metadata = []): InternalWalletEntry
    {
        return $this->recordEntry(
            type: InternalWalletEntryType::EscrowHeld,
            direction: InternalWalletDirection::Neutral,
            amount: (float) $escrow->amount,
            status: TransactionStatus::Completed,
            affectsBalance: false,
            occurredAt: $occurredAt ?? $escrow->created_at ?? now(),
            sourceType: Escrow::class,
            sourceId: $escrow->id,
            orderId: $escrow->orderItem?->order_id,
            idempotencyKey: sprintf('internal-wallet:escrow-held:%d', $escrow->id),
            metadata: $metadata,
        );
    }

    public function escrowReleased(Escrow $escrow, ?Carbon $occurredAt = null, array $metadata = []): InternalWalletEntry
    {
        return $this->recordEntry(
            type: InternalWalletEntryType::EscrowReleased,
            direction: InternalWalletDirection::Neutral,
            amount: (float) $escrow->amount,
            status: TransactionStatus::Completed,
            affectsBalance: false,
            occurredAt: $occurredAt ?? $escrow->updated_at ?? now(),
            sourceType: Escrow::class,
            sourceId: $escrow->id,
            orderId: $escrow->orderItem?->order_id,
            idempotencyKey: sprintf('internal-wallet:escrow-released:%d', $escrow->id),
            metadata: $metadata,
        );
    }

    public function refundPaid(Complaint $complaint, ?Carbon $occurredAt = null): InternalWalletEntry
    {
        $amount = (float) ($complaint->orderItem?->subtotal ?? 0);

        return $this->recordEntry(
            type: InternalWalletEntryType::RefundPaid,
            direction: InternalWalletDirection::Outflow,
            amount: $amount,
            status: TransactionStatus::Completed,
            affectsBalance: true,
            occurredAt: $occurredAt ?? $complaint->resolved_at ?? $complaint->updated_at ?? now(),
            sourceType: Complaint::class,
            sourceId: $complaint->id,
            orderId: $complaint->orderItem?->order_id,
            idempotencyKey: sprintf('internal-wallet:refund-paid:complaint:%d', $complaint->id),
            metadata: [
                'complaint_code' => $complaint->complaint_code,
                'order_item_id'  => $complaint->order_item_id,
            ],
        );
    }

    public function sellerPayoutRequested(Withdraw $withdraw, ?TransactionStatus $status = null): InternalWalletEntry
    {
        $entry = InternalWalletEntry::query()
            ->where('idempotency_key', $this->withdrawRequestKey($withdraw))
            ->first();

        if ($entry !== null) {
            if ($status !== null && $entry->status !== $status) {
                $entry->forceFill([
                    'status' => $status,
                ])->save();
            }

            return $entry;
        }

        return $this->recordEntry(
            type: InternalWalletEntryType::SellerPayoutRequested,
            direction: InternalWalletDirection::Outflow,
            amount: (float) $withdraw->amount,
            status: $status ?? TransactionStatus::Pending,
            affectsBalance: false,
            occurredAt: $withdraw->created_at ?? now(),
            sourceType: Withdraw::class,
            sourceId: $withdraw->id,
            orderId: null,
            idempotencyKey: $this->withdrawRequestKey($withdraw),
            metadata: [
                'withdraw_status' => $withdraw->status->name,
                'bank_name'       => $withdraw->bank_name,
            ],
        );
    }

    public function sellerPayoutCompleted(Withdraw $withdraw, ?Carbon $occurredAt = null, array $metadata = []): InternalWalletEntry
    {
        $this->updateWithdrawalRequestStatus($withdraw, TransactionStatus::Completed, $metadata);

        return $this->recordEntry(
            type: InternalWalletEntryType::SellerPayoutCompleted,
            direction: InternalWalletDirection::Outflow,
            amount: (float) $withdraw->amount,
            status: TransactionStatus::Completed,
            affectsBalance: true,
            occurredAt: $occurredAt ?? $withdraw->processed_at ?? now(),
            sourceType: Withdraw::class,
            sourceId: $withdraw->id,
            orderId: null,
            idempotencyKey: sprintf('internal-wallet:seller-payout-completed:%d', $withdraw->id),
            metadata: $metadata,
        );
    }

    public function sellerPayoutFailed(Withdraw $withdraw, ?Carbon $occurredAt = null, array $metadata = []): InternalWalletEntry
    {
        $this->updateWithdrawalRequestStatus($withdraw, $this->withdrawStatusToTransactionStatus($withdraw->status), $metadata);

        return $this->recordEntry(
            type: InternalWalletEntryType::SellerPayoutFailed,
            direction: InternalWalletDirection::Outflow,
            amount: (float) $withdraw->amount,
            status: $this->withdrawStatusToTransactionStatus($withdraw->status),
            affectsBalance: false,
            occurredAt: $occurredAt ?? $withdraw->processed_at ?? now(),
            sourceType: Withdraw::class,
            sourceId: $withdraw->id,
            orderId: null,
            idempotencyKey: sprintf('internal-wallet:seller-payout-failed:%d', $withdraw->id),
            metadata: $metadata,
        );
    }

    public function wallet(): Wallet
    {
        return Wallet::firstOrCreate(
            ['seller_id' => null, 'type' => WalletType::Internal],
            ['code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function recordEntry(
        InternalWalletEntryType $type,
        InternalWalletDirection $direction,
        float $amount,
        TransactionStatus $status,
        bool $affectsBalance,
        Carbon $occurredAt,
        ?string $sourceType,
        ?int $sourceId,
        ?int $orderId,
        ?string $idempotencyKey,
        array $metadata = [],
    ): InternalWalletEntry {
        return DB::transaction(function () use ($type, $direction, $amount, $status, $affectsBalance, $occurredAt, $sourceType, $sourceId, $orderId, $idempotencyKey, $metadata): InternalWalletEntry {
            if ($idempotencyKey !== null) {
                $existingEntry = InternalWalletEntry::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingEntry !== null) {
                    return $existingEntry;
                }
            }

            $wallet = Wallet::query()
                ->whereKey($this->wallet()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($affectsBalance && $status === TransactionStatus::Completed) {
                $signedAmount = match ($direction) {
                    InternalWalletDirection::Inflow  => $amount,
                    InternalWalletDirection::Outflow => -$amount,
                    InternalWalletDirection::Neutral => 0.0,
                };

                $wallet->forceFill([
                    'balance' => round((float) $wallet->balance + $signedAmount, 2),
                ])->save();
            }

            return InternalWalletEntry::query()->create([
                'wallet_id'       => $wallet->id,
                'order_id'        => $orderId,
                'source_type'     => $sourceType,
                'source_id'       => $sourceId,
                'type'            => $type,
                'direction'       => $direction,
                'amount'          => round($amount, 2),
                'status'          => $status,
                'affects_balance' => $affectsBalance,
                'idempotency_key' => $idempotencyKey,
                'metadata'        => $metadata,
                'occurred_at'     => $occurredAt,
            ]);
        }, 5);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function updateWithdrawalRequestStatus(Withdraw $withdraw, TransactionStatus $status, array $metadata = []): void
    {
        $entry = $this->sellerPayoutRequested($withdraw);

        $entry->forceFill([
            'status'   => $status,
            'metadata' => array_merge($entry->metadata ?? [], $metadata),
        ])->save();
    }

    protected function withdrawRequestKey(Withdraw $withdraw): string
    {
        return sprintf('internal-wallet:seller-payout-requested:%d', $withdraw->id);
    }

    protected function withdrawStatusToTransactionStatus(WithdrawStatus $status): TransactionStatus
    {
        return match ($status) {
            WithdrawStatus::Completed => TransactionStatus::Completed,
            WithdrawStatus::Rejected, WithdrawStatus::Cancelled => TransactionStatus::Cancelled,
            WithdrawStatus::Failed => TransactionStatus::Failed,
            default                => TransactionStatus::Pending,
        };
    }
}
