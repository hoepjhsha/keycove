<?php

declare(strict_types=1);

use App\Enums\KycStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Enums\WithdrawStatus;
use App\Livewire\Admin\Action\Withdraw\WithdrawalRequestIndex;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use App\Services\InternalWalletService;
use Livewire\Livewire;

it('admin can access withdrawal requests page', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(WithdrawalRequestIndex::class)
        ->assertOk()
        ->assertSee('Yêu cầu rút tiền');
});

it('admin can view withdrawal details', function (): void {
    $admin = User::factory()->admin()->create();
    [, , $withdraw] = pendingWithdrawalFixture();

    Livewire::actingAs($admin, 'admin')
        ->test(WithdrawalRequestIndex::class)
        ->call('viewWithdrawal', $withdraw->id)
        ->assertSet('showViewModal', true)
        ->assertSee('Vietcombank')
        ->assertSee('Nguyen Van A');
});

it('admin can approve a pending withdrawal request', function (): void {
    config()->set('services.payment.default', 'vnpay');
    config()->set('services.payment.vnpay.withdraw_mock', true);
    config()->set('queue.default', 'sync');

    Wallet::query()->create([
        'seller_id' => null,
        'type'      => WalletType::Internal,
        'code'      => 'INT-WDR-001',
        'balance'   => 10000,
        'holding'   => 0,
    ]);

    $admin = User::factory()->admin()->create();
    [$sellerUser, $wallet, $withdraw] = pendingWithdrawalFixture();

    Livewire::actingAs($admin, 'admin')
        ->test(WithdrawalRequestIndex::class)
        ->call('approve', $withdraw->id)
        ->assertHasNoErrors();

    expect($wallet->fresh()->balance)->toBe('11000.00')
        ->and($wallet->fresh()->holding)->toBe('2000.00')
        ->and($withdraw->fresh()->status)->toBe(WithdrawStatus::Completed)
        ->and($withdraw->fresh()->processed_by)->toBe($admin->id)
        ->and($wallet->transactions()->latest('id')->first()?->status)->toBe(TransactionStatus::Completed)
        ->and(Wallet::query()->where('type', WalletType::Internal)->value('balance'))->toBe('9000.00');
});

it('admin can reject a pending withdrawal request', function (): void {
    $admin = User::factory()->admin()->create();
    [, $wallet, $withdraw] = pendingWithdrawalFixture();

    Livewire::actingAs($admin, 'admin')
        ->test(WithdrawalRequestIndex::class)
        ->set('rejectingWithdrawId', $withdraw->id)
        ->set('rejectReason', 'Sai thông tin tài khoản ngân hàng')
        ->call('reject')
        ->assertHasNoErrors();

    $transaction = $wallet->transactions()->latest('id')->first();

    expect($wallet->fresh()->balance)->toBe('12000.00')
        ->and($wallet->fresh()->holding)->toBe('2000.00')
        ->and($withdraw->fresh()->status)->toBe(WithdrawStatus::Rejected)
        ->and($withdraw->fresh()->reject_reason)->toBe('Sai thông tin tài khoản ngân hàng')
        ->and($transaction?->type)->toBe(TransactionType::Withdraw)
        ->and($transaction?->status)->toBe(TransactionStatus::Cancelled);
});

function pendingWithdrawalFixture(): array
{
    $sellerUser = User::factory()->seller()->create();
    $seller = Seller::query()->create([
        'user_id'             => $sellerUser->id,
        'shop_name'           => 'KeyCove Store',
        'cccd_number'         => '123456789012',
        'cccd_front_image'    => null,
        'cccd_back_image'     => null,
        'kyc_status'          => KycStatus::Approved,
        'kyc_rejected_reason' => null,
    ]);

    $wallet = Wallet::query()->create([
        'seller_id' => $seller->id,
        'type'      => WalletType::Seller,
        'code'      => 'SELLER-PENDING-001',
        'balance'   => 11_000,
        'holding'   => 3_000,
    ]);

    $withdraw = $wallet->withdraws()->create([
        'amount'              => 1000,
        'status'              => WithdrawStatus::Pending,
        'bank_name'           => 'Vietcombank',
        'bank_account_number' => '0123456789',
        'bank_account_name'   => 'Nguyen Van A',
        'requested_by'        => $sellerUser->id,
        'metadata'            => ['bank_code' => 'VCB'],
    ]);

    $wallet->transactions()->create([
        'type'         => TransactionType::Withdraw,
        'balance_type' => TransactionBalanceType::WithdrawPending,
        'source_type'  => Withdraw::class,
        'source_id'    => $withdraw->id,
        'payment_info' => [
            'withdraw_id'     => $withdraw->id,
            'withdraw_status' => WithdrawStatus::Pending->name,
        ],
        'amount'   => -1000,
        'status'   => TransactionStatus::Pending,
        'metadata' => [
            'withdraw_id' => $withdraw->id,
        ],
    ]);

    app(InternalWalletService::class)->sellerPayoutRequested($withdraw);

    return [$sellerUser, $wallet, $withdraw];
}
