<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\ComplaintStatus;
use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\WalletType;
use App\Events\ComplaintThreadUpdated;
use App\Managers\PaymentManager;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\ComplaintActivityNotification;
use App\Services\InternalWalletService;
use App\Utilities\StorageUtility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ComplaintService
{
    public function __construct(
        private InternalWalletService $internalWalletService,
        private PaymentManager $paymentManager,
    ) {}

    public function openComplaint(OrderItem $item, User $actor, string $reason, array $evidence = []): Complaint
    {
        $complaint = DB::transaction(function () use ($item, $actor, $reason, $evidence): Complaint {
            $complaint = Complaint::create([
                'order_item_id'  => $item->id,
                'reason'         => $reason,
                'evidence'       => $this->storeUploadedFiles($evidence, 'complaints/evidence'),
                'status'         => ComplaintStatus::Open,
                'complaint_code' => 'CMP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            ]);

            ComplaintMessage::create([
                'complaint_id' => $complaint->id,
                'sender_id'    => $actor->id,
                'message'      => $reason,
                'attachments'  => [],
            ]);

            $item->forceFill([
                'status' => OrderStatus::Disputing,
            ])->save();

            return $complaint;
        });

        $this->notifyActivity(
            $complaint->load(['orderItem.order.buyer', 'orderItem.seller.user']),
            $actor,
            'Complaint opened',
            'A new complaint was opened for '.$this->complaintSummary($complaint).'.'
        );

        $this->broadcastThreadUpdate($complaint, $actor, 'opened');

        return $complaint;
    }

    public function reply(Complaint $complaint, User $actor, string $message, array $attachments = []): ComplaintMessage
    {
        $complaint->loadMissing(['orderItem.order.buyer', 'orderItem.seller.user']);

        $messageRecord = DB::transaction(function () use ($complaint, $actor, $message, $attachments): ComplaintMessage {
            if ($complaint->status === ComplaintStatus::Open) {
                $complaint->forceFill(['status' => ComplaintStatus::InProcess])->save();
            }

            return ComplaintMessage::create([
                'complaint_id' => $complaint->id,
                'sender_id'    => $actor->id,
                'message'      => $message,
                'attachments'  => $this->storeUploadedFiles($attachments, 'complaints/messages'),
            ]);
        });

        $this->notifyActivity(
            $complaint,
            $actor,
            'Complaint updated',
            ($actor->username ?? 'Someone').' added a new message to '.$this->complaintSummary($complaint).'.'
        );

        $this->broadcastThreadUpdate($complaint, $actor, 'replied', $messageRecord->id);

        return $messageRecord;
    }

    public function resolveRefund(Complaint $complaint, User $actor, string $resolutionNote, ?string $resolvedAt = null): Complaint
    {
        return $this->resolve($complaint, $actor, $resolutionNote, ComplaintStatus::ApprovedRefund, OrderStatus::Refunded, $resolvedAt);
    }

    public function refundComplaint(Complaint $complaint, User $actor, string $resolutionNote, ?string $resolvedAt = null): Complaint
    {
        $complaint->loadMissing(['orderItem.order.paymentTransactions', 'orderItem.escrow']);

        $order = $complaint->orderItem?->order;

        if ($order === null || $order->payment_method !== PaymentMethod::VNPay) {
            throw new RuntimeException((string) __('admin.messages.refund_vnpay_only'));
        }

        $paymentTransaction = $order->paymentTransactions()
            ->where('status', PaymentStatus::Completed)
            ->latest('id')
            ->first();

        if ($paymentTransaction === null) {
            throw new RuntimeException((string) __('admin.messages.refund_payment_not_found'));
        }

        $transactionNumber = $this->resolveRefundTransactionNumber($paymentTransaction);

        if ($transactionNumber === null) {
            throw new RuntimeException((string) __('admin.messages.refund_payment_not_found'));
        }

        $refundResponse = $this->paymentManager->driver('vnpay')->refund([
            'txn_ref'          => $order->order_code,
            'amount'           => (float) ($complaint->orderItem?->subtotal ?? 0),
            'order_info'       => 'Refund complaint #'.$complaint->id,
            'transaction_no'   => $transactionNumber,
            'transaction_date' => $this->resolveRefundTransactionDate($paymentTransaction),
            'create_by'        => (string) $actor->id,
            'ip_address'       => request()->ip(),
        ]);

        if (! ($refundResponse['success'] ?? false)) {
            throw new RuntimeException((string) ($refundResponse['message'] ?? 'Refund request was rejected by the payment gateway.'));
        }

        return $this->resolve(
            complaint: $complaint,
            actor: $actor,
            resolutionNote: $resolutionNote,
            status: ComplaintStatus::ApprovedRefund,
            orderStatus: OrderStatus::Refunded,
            resolvedAt: $resolvedAt,
            refundData: $refundResponse,
        );
    }

    public function resolveRelease(Complaint $complaint, User $actor, string $resolutionNote, ?string $resolvedAt = null): Complaint
    {
        return $this->resolve($complaint, $actor, $resolutionNote, ComplaintStatus::RejectedRelease, OrderStatus::Completed, $resolvedAt);
    }

    protected function resolve(
        Complaint $complaint,
        User $actor,
        string $resolutionNote,
        ComplaintStatus $status,
        OrderStatus $orderStatus,
        ?string $resolvedAt = null,
        ?array $refundData = null,
    ): Complaint {
        $complaint->loadMissing(['orderItem.order.buyer', 'orderItem.order.paymentTransactions', 'orderItem.seller.user', 'orderItem.escrow']);
        $resolvedAt = Carbon::parse($resolvedAt ?? now());

        $resolvedComplaint = DB::transaction(function () use ($complaint, $actor, $resolutionNote, $status, $orderStatus, $resolvedAt, $refundData): Complaint {
            $complaint = Complaint::query()
                ->whereKey($complaint->id)
                ->lockForUpdate()
                ->with(['orderItem.order.buyer', 'orderItem.order.paymentTransactions', 'orderItem.seller.user', 'orderItem.escrow'])
                ->firstOrFail();

            $complaint->forceFill([
                'status'          => $status,
                'resolved_by'     => $actor->id,
                'resolution_note' => $resolutionNote,
                'resolved_at'     => $resolvedAt,
            ])->save();

            $orderItem = $complaint->orderItem;
            $order = $orderItem?->order;
            $escrow = $orderItem?->escrow;
            $paymentTransaction = $order?->paymentTransactions?->sortByDesc('id')->first();

            $orderItem?->forceFill([
                'status'       => $orderStatus,
                'completed_at' => $orderStatus === OrderStatus::Completed ? $resolvedAt : $orderItem?->completed_at,
            ])->save();

            if ($status === ComplaintStatus::ApprovedRefund && $paymentTransaction !== null && $refundData !== null) {
                $paymentTransaction->forceFill([
                    'response_payload' => array_merge($paymentTransaction->response_payload ?? [], [
                        'refund' => $refundData,
                    ]),
                ])->save();
            }

            $escrow?->forceFill([
                'status' => $status === ComplaintStatus::ApprovedRefund ? EscrowStatus::Refunded : EscrowStatus::Released,
            ])->save();

            if ($orderItem?->seller_id !== null && $escrow !== null) {
                $wallet = Wallet::firstOrCreate(
                    ['seller_id' => $orderItem->seller_id],
                    ['type' => WalletType::Seller, 'code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
                );

                $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

                $amount = (float) $escrow->amount;

                if ($status === ComplaintStatus::ApprovedRefund) {
                    $wallet->forceFill([
                        'holding' => round((float) $wallet->holding - $amount, 2),
                    ])->save();

                    $wallet->transactions()->create([
                        'order_id'     => $orderItem->order_id,
                        'source_type'  => Escrow::class,
                        'source_id'    => $escrow->id,
                        'type'         => TransactionType::Refund,
                        'balance_type' => TransactionBalanceType::Holding,
                        'payment_info' => [
                            'complaint_code' => $complaint->complaint_code,
                            'resolution'     => 'refund',
                            'gateway_refund' => $refundData,
                        ],
                        'amount'   => -$amount,
                        'status'   => TransactionStatus::Completed,
                        'metadata' => [
                            'complaint_id'  => $complaint->id,
                            'order_item_id' => $orderItem->id,
                        ],
                    ]);

                    $this->internalWalletService->refundPaid($complaint, $complaint->resolved_at ?? now());
                } else {
                    $wallet->forceFill([
                        'holding' => round((float) $wallet->holding - $amount, 2),
                        'balance' => round((float) $wallet->balance + $amount, 2),
                    ])->save();

                    $wallet->transactions()->create([
                        'order_id'     => $orderItem->order_id,
                        'source_type'  => Escrow::class,
                        'source_id'    => $escrow->id,
                        'type'         => TransactionType::EscrowRelease,
                        'balance_type' => TransactionBalanceType::Available,
                        'payment_info' => [
                            'complaint_code' => $complaint->complaint_code,
                            'resolution'     => 'release',
                        ],
                        'amount'   => $amount,
                        'status'   => TransactionStatus::Completed,
                        'metadata' => [
                            'complaint_id'  => $complaint->id,
                            'order_item_id' => $orderItem->id,
                        ],
                    ]);

                    $this->internalWalletService->escrowReleased($escrow, $complaint->resolved_at ?? now(), [
                        'complaint_id'  => $complaint->id,
                        'order_item_id' => $orderItem->id,
                        'resolution'    => 'release',
                    ]);
                }
            } elseif ($status === ComplaintStatus::ApprovedRefund && $orderItem !== null) {
                $internalWallet = Wallet::query()
                    ->whereKey($this->internalWalletService->wallet()->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $amount = (float) $orderItem->subtotal;

                $internalWallet->transactions()->create([
                    'order_id'     => $orderItem->order_id,
                    'source_type'  => Complaint::class,
                    'source_id'    => $complaint->id,
                    'type'         => TransactionType::Refund,
                    'balance_type' => TransactionBalanceType::Available,
                    'payment_info' => [
                        'complaint_code' => $complaint->complaint_code,
                        'resolution'     => 'refund',
                        'gateway_refund' => $refundData,
                    ],
                    'amount'   => -$amount,
                    'status'   => TransactionStatus::Completed,
                    'metadata' => [
                        'complaint_id'  => $complaint->id,
                        'order_item_id' => $orderItem->id,
                    ],
                ]);

                $this->internalWalletService->refundPaid($complaint, $complaint->resolved_at ?? now());
            }

            if ($status === ComplaintStatus::ApprovedRefund && $order instanceof Order) {
                $this->syncRefundedPaymentStatus($order);
            }

            return $complaint;
        }, 5);

        $this->notifyActivity(
            $resolvedComplaint,
            $actor,
            $status === ComplaintStatus::ApprovedRefund ? 'Complaint approved' : 'Complaint rejected',
            ($actor->username ?? 'Admin').' resolved '.$this->complaintSummary($resolvedComplaint).' as '.($status === ComplaintStatus::ApprovedRefund ? 'refund approved' : 'funds released').'.'
        );

        $this->broadcastThreadUpdate($resolvedComplaint, $actor, $status === ComplaintStatus::ApprovedRefund ? 'refund-approved' : 'release-approved');

        return $resolvedComplaint;
    }

    protected function resolveRefundTransactionNumber(PaymentTransaction $paymentTransaction): ?string
    {
        return data_get($paymentTransaction->response_payload, 'vnp_TransactionNo')
            ?? data_get($paymentTransaction->response_payload, 'transaction_no');
    }

    protected function resolveRefundTransactionDate(PaymentTransaction $paymentTransaction): string
    {
        return data_get($paymentTransaction->response_payload, 'vnp_PayDate')
            ?? data_get($paymentTransaction->response_payload, 'pay_date')
            ?? ($paymentTransaction->paid_at?->format('YmdHis') ?? now()->format('YmdHis'));
    }

    protected function syncRefundedPaymentStatus(Order $order): void
    {
        $lockedOrder = Order::query()
            ->whereKey($order->id)
            ->lockForUpdate()
            ->with(['items', 'paymentTransactions'])
            ->first();

        if (! $lockedOrder instanceof Order) {
            return;
        }

        $allItemsRefunded = $lockedOrder->items->isNotEmpty()
            && $lockedOrder->items->every(fn (OrderItem $item): bool => $item->status === OrderStatus::Refunded);

        if (! $allItemsRefunded) {
            return;
        }

        $lockedOrder->forceFill([
            'payment_status' => PaymentStatus::Refunded,
        ])->save();

        $lockedOrder->paymentTransactions
            ->sortByDesc('id')
            ->first()?->forceFill([
                'status' => PaymentStatus::Refunded,
            ])->save();
    }

    protected function broadcastThreadUpdate(Complaint $complaint, User $actor, string $action, ?int $messageId = null): void
    {
        event(new ComplaintThreadUpdated(
            complaintId: $complaint->id,
            actorId: $actor->id,
            action: $action,
            messageId: $messageId,
        ));
    }

    /**
     * @param  array<int, mixed>  $files
     * @return list<string>
     */
    protected function storeUploadedFiles(array $files, string $directory): array
    {
        return collect($files)
            ->filter()
            ->map(function ($file) use ($directory): string|false {
                return StorageUtility::store($file, $directory, config('filesystems.public_disk'));
            })
            ->filter(fn (string|false $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();
    }

    protected function notifyActivity(Complaint $complaint, User $actor, string $headline, string $body): void
    {
        $complaint->loadMissing(['orderItem.order.buyer', 'orderItem.seller.user']);

        $recipients = $this->recipientsForComplaint($complaint, $actor);

        if ($recipients->isEmpty()) {
            return;
        }

        $payload = [
            'complaint_id'   => $complaint->id,
            'complaint_code' => $complaint->complaint_code,
            'event'          => $headline,
            'actor_name'     => $actor->username,
            'order_code'     => $complaint->orderItem?->order?->order_code,
            'product_name'   => $complaint->orderItem?->product_name_snapshot,
        ];

        $recipients->each(function (User $recipient) use ($complaint, $headline, $body, $payload): void {
            $recipient->notify(new ComplaintActivityNotification(
                headline: $headline,
                body: $body,
                url: $this->threadUrl($complaint, $recipient),
                payload: $payload,
            ));
        });
    }

    protected function recipientsForComplaint(Complaint $complaint, User $actor): Collection
    {
        $recipients = collect();

        $buyer = $complaint->orderItem?->order?->buyer;
        $seller = $complaint->orderItem?->seller?->user;

        if ($buyer instanceof User && $buyer->id !== $actor->id) {
            $recipients->push($buyer);
        }

        if ($seller instanceof User && $seller->id !== $actor->id) {
            $recipients->push($seller);
        }

        $admins = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
            ->where('id', '!=', $actor->id)
            ->get();

        return $recipients
            ->merge($admins)
            ->unique('id')
            ->values();
    }

    protected function threadUrl(Complaint $complaint, User $recipient): string
    {
        if (in_array($recipient->role, [UserRole::Admin, UserRole::SuperAdmin], true)) {
            return url('/admin/complaints/'.($complaint->complaint_code ?: $complaint->id));
        }

        $routeName = $complaint->orderItem?->seller_id !== null && $recipient->seller?->id === $complaint->orderItem?->seller_id
            ? 'seller.complaints.show'
            : 'app.library.complaints.show';

        return route($routeName, ['complaint' => $complaint->complaint_code ?: $complaint->id]);
    }

    protected function complaintSummary(Complaint $complaint): string
    {
        $product = $complaint->orderItem?->product_name_snapshot ?? 'complaint';

        return '#'.$complaint->complaint_code.' for '.$product;
    }
}
