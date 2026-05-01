<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Managers\PaymentManager;
use App\Models\Seller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use App\Services\InternalWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SellerWithdrawalService
{
    public function __construct(
        private PaymentManager $payments,
        private InternalWalletService $internalWalletService,
    ) {}

    /**
     * @param  array{amount: float|int|string, bank_name: string, bank_code: string, bank_account_number: string, bank_account_name: string}  $data
     */
    public function requestWithdrawal(Seller $seller, User $actor, array $data): Withdraw
    {
        $amount = round((float) $data['amount'], 2);

        $wallet = Wallet::firstOrCreate(
            ['seller_id' => $seller->id],
            ['type' => WalletType::Seller, 'code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
        );

        $withdraw = null;
        $transaction = null;

        DB::transaction(function () use (&$withdraw, &$transaction, $wallet, $actor, $data, $amount): void {
            $lockedWallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ((float) $lockedWallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient available balance.',
                ]);
            }

            $lockedWallet->forceFill([
                'balance' => round((float) $lockedWallet->balance - $amount, 2),
                'holding' => round((float) $lockedWallet->holding + $amount, 2),
            ])->save();

            $withdraw = $lockedWallet->withdraws()->create([
                'amount'              => $amount,
                'status'              => WithdrawStatus::Processing,
                'bank_name'           => $data['bank_name'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_account_name'   => $data['bank_account_name'],
                'requested_by'        => $actor->id,
                'metadata'            => [
                    'bank_code' => $data['bank_code'],
                    'gateway'   => 'vnpay',
                ],
            ]);

            $transaction = $lockedWallet->transactions()->create([
                'order_id'     => null,
                'source_type'  => Withdraw::class,
                'source_id'    => $withdraw->id,
                'type'         => TransactionType::Withdraw,
                'balance_type' => TransactionBalanceType::WithdrawPending,
                'payment_info' => [
                    'bank_name'       => $data['bank_name'],
                    'bank_code'       => $data['bank_code'],
                    'account_number'  => $data['bank_account_number'],
                    'account_holder'  => $data['bank_account_name'],
                    'withdraw_id'     => $withdraw->id,
                    'withdraw_status' => WithdrawStatus::Processing->name,
                ],
                'amount'   => -$amount,
                'status'   => TransactionStatus::Pending,
                'metadata' => [
                    'withdraw_id' => $withdraw->id,
                ],
            ]);
        });

        if ($withdraw !== null) {
            $this->internalWalletService->sellerPayoutRequested($withdraw);
        }

        $response = $this->performWithdrawal($data, $withdraw);

        $this->finalizeWithdrawal($withdraw, $transaction, $amount, $response);

        return $withdraw->fresh();
    }

    /**
     * @param  array{amount: float|int|string, bank_name: string, bank_code: string, bank_account_number: string, bank_account_name: string}  $data
     * @return array{success: bool, message: string, payload?: array<string, mixed>}
     */
    protected function performWithdrawal(array $data, ?Withdraw $withdraw): array
    {
        try {
            return $this->payments->driver('vnpay')->withdraw([
                'amount'         => (float) $data['amount'],
                'bank_code'      => $data['bank_code'],
                'account_number' => $data['bank_account_number'],
                'account_name'   => $data['bank_account_name'],
                'order_info'     => 'Yêu cầu rút tiền người bán'.($withdraw ? ' #'.$withdraw->id : ''),
                'txn_ref'        => $withdraw ? 'WD-'.$withdraw->id : null,
                'request_id'     => $withdraw ? 'WDREQ-'.$withdraw->id : null,
            ]);
        } catch (Throwable $throwable) {
            return [
                'success' => false,
                'message' => $throwable->getMessage(),
            ];
        }
    }

    /**
     * @param  array{success: bool, message: string, payload?: array<string, mixed>}  $response
     */
    protected function finalizeWithdrawal(Withdraw $withdraw, Transaction $transaction, float $amount, array $response): void
    {
        DB::transaction(function () use ($withdraw, $transaction, $amount, $response): void {
            $lockedWithdraw = Withdraw::query()->whereKey($withdraw->id)->lockForUpdate()->firstOrFail();
            $lockedWallet = Wallet::query()->whereKey($lockedWithdraw->wallet_id)->lockForUpdate()->firstOrFail();
            $lockedTransaction = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            $metadata = array_filter([
                'bank_code'        => $lockedWithdraw->metadata['bank_code'] ?? null,
                'gateway_response' => $response['payload'] ?? null,
                'gateway_message'  => $response['message'] ?? null,
            ], fn (mixed $value): bool => $value !== null);

            if ($response['success']) {
                $lockedWallet->forceFill([
                    'holding' => round((float) $lockedWallet->holding - $amount, 2),
                ])->save();

                $lockedWithdraw->forceFill([
                    'status'       => WithdrawStatus::Completed,
                    'processed_at' => now(),
                    'metadata'     => array_merge($lockedWithdraw->metadata ?? [], $metadata),
                ])->save();

                $lockedTransaction->forceFill([
                    'status'   => TransactionStatus::Completed,
                    'metadata' => array_merge($lockedTransaction->metadata ?? [], $metadata),
                ])->save();

                $this->internalWalletService->sellerPayoutCompleted($lockedWithdraw, $lockedWithdraw->processed_at ?? now(), $metadata);

                return;
            }

            $lockedWallet->forceFill([
                'balance' => round((float) $lockedWallet->balance + $amount, 2),
                'holding' => round((float) $lockedWallet->holding - $amount, 2),
            ])->save();

            $lockedWithdraw->forceFill([
                'status'       => WithdrawStatus::Failed,
                'processed_at' => now(),
                'metadata'     => array_merge($lockedWithdraw->metadata ?? [], $metadata),
            ])->save();

            $lockedTransaction->forceFill([
                'status'   => TransactionStatus::Failed,
                'metadata' => array_merge($lockedTransaction->metadata ?? [], $metadata),
            ])->save();

            $this->internalWalletService->sellerPayoutFailed($lockedWithdraw, $lockedWithdraw->processed_at ?? now(), $metadata);
        });
    }
}
