<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\WithdrawStatus;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Shop\SellerWithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Rút tiền người bán')]
class Withdrawals extends Component
{
    public string $amount = '';

    public string $bankName = '';

    public string $bankCode = '';

    public string $bankAccountNumber = '';

    public string $bankAccountName = '';

    public function submit(SellerWithdrawalService $service): void
    {
        $user = $this->resolveUser();
        $seller = $this->resolveSeller($user);
        $maximumWithdrawal = $this->maximumWithdrawalAmount($seller);

        $validated = $this->validate([
            'amount'            => ['required', 'numeric', 'decimal:0,2', 'min:'.$this->minimumWithdrawalAmount(), 'max:'.$maximumWithdrawal],
            'bankName'          => ['required', 'string', 'max:255'],
            'bankCode'          => ['required', 'string', 'max:50'],
            'bankAccountNumber' => ['required', 'string', 'max:255'],
            'bankAccountName'   => ['required', 'string', 'max:255'],
        ]);

        try {
            $withdrawal = $service->requestWithdrawal($seller, $user, [
                'amount'              => $validated['amount'],
                'bank_name'           => $validated['bankName'],
                'bank_code'           => $validated['bankCode'],
                'bank_account_number' => $validated['bankAccountNumber'],
                'bank_account_name'   => $validated['bankAccountName'],
            ]);
        } catch (Throwable $throwable) {
            if ($throwable instanceof ValidationException) {
                throw $throwable;
            }

            report($throwable);

            session()->flash('withdraw-error', 'Yêu cầu rút tiền của bạn gặp lỗi không mong muốn.');

            return;
        }

        $this->reset(['amount', 'bankName', 'bankCode', 'bankAccountNumber', 'bankAccountName']);

        if ($withdrawal->status === WithdrawStatus::Completed) {
            session()->flash('withdraw-status', 'Yêu cầu rút tiền của bạn đã hoàn tất.');

            return;
        }

        session()->flash('withdraw-error', 'Không thể hoàn tất yêu cầu rút tiền. Số dư của bạn đã được khôi phục.');
    }

    public function render(): View
    {
        $user = $this->resolveUser();
        $user->loadMissing('seller.wallet');

        $seller = $this->resolveSeller($user);

        return view('pages.shop.seller.withdrawals.index', [
            'user'              => $user,
            'seller'            => $seller,
            'wallet'            => $seller->wallet,
            'metrics'           => $this->metrics($seller),
            'withdrawals'       => $this->withdrawals($seller),
            'minimumWithdrawal' => $this->minimumWithdrawalAmount(),
            'maximumWithdrawal' => $this->maximumWithdrawalAmount($seller),
        ])->layout('components.layouts.seller', [
            'title'         => 'Rút tiền người bán',
            'user'          => $user,
            'seller'        => $seller,
            'activeSection' => 'withdrawals',
        ]);
    }

    protected function metrics(Seller $seller): array
    {
        $financialSummary = $this->financialSummary($seller);

        return [
            'walletBalance'  => (float) ($seller->wallet?->balance ?? 0),
            'walletHolding'  => (float) ($seller->wallet?->holding ?? 0),
            'sellerEarnings' => $financialSummary['seller_earnings'],
            'platformFee'    => $financialSummary['platform_fee'],
        ];
    }

    /**
     * @return array{seller_earnings: float, platform_fee: float}
     */
    protected function financialSummary(Seller $seller): array
    {
        $summary = OrderItem::query()
            ->where('seller_id', $seller->id)
            ->whereIn('status', [
                OrderStatus::Delivered->value,
                OrderStatus::Disputing->value,
                OrderStatus::Completed->value,
            ])
            ->selectRaw('COALESCE(SUM(seller_amount), 0) as seller_earnings, COALESCE(SUM(platform_fee), 0) as platform_fee')
            ->first();

        return [
            'seller_earnings' => (float) ($summary?->seller_earnings ?? 0),
            'platform_fee'    => (float) ($summary?->platform_fee ?? 0),
        ];
    }

    /**
     * @return Collection<int, Withdraw>
     */
    protected function withdrawals(Seller $seller): Collection
    {
        return $seller->withdraws()
            ->with(['requestedBy', 'processedBy'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    protected function minimumWithdrawalAmount(): float
    {
        $configuredValue = SystemConfig::query()
            ->where('key', 'min_withdrawal_amount')
            ->value('value');

        return max(0.0, (float) ($configuredValue ?: 50));
    }

    protected function maximumWithdrawalAmount(Seller $seller): float
    {
        $configuredValue = SystemConfig::query()
            ->where('key', 'max_withdrawal_amount')
            ->value('value');

        $configuredMax = max(0.0, (float) ($configuredValue ?: 10000));
        $walletBalance = (float) ($seller->wallet?->balance ?? 0);

        return min($configuredMax, $walletBalance);
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function resolveSeller(User $user): Seller
    {
        $seller = $user->seller;

        abort_unless($seller instanceof Seller, 403);
        abort_unless($user->role === UserRole::Seller && $seller->kyc_status === KycStatus::Approved, 403);

        return $seller;
    }
}
