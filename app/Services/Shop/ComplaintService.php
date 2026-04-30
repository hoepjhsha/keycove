<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Enums\ComplaintStatus;
use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\ComplaintActivityNotification;
use App\Utilities\StorageUtility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
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

        return $messageRecord;
    }

    public function resolveRefund(Complaint $complaint, User $actor, string $resolutionNote, ?string $resolvedAt = null): Complaint
    {
        return $this->resolve($complaint, $actor, $resolutionNote, ComplaintStatus::ApprovedRefund, OrderStatus::Refunded, $resolvedAt);
    }

    public function resolveRelease(Complaint $complaint, User $actor, string $resolutionNote, ?string $resolvedAt = null): Complaint
    {
        return $this->resolve($complaint, $actor, $resolutionNote, ComplaintStatus::RejectedRelease, OrderStatus::Completed, $resolvedAt);
    }

    protected function resolve(Complaint $complaint, User $actor, string $resolutionNote, ComplaintStatus $status, OrderStatus $orderStatus, ?string $resolvedAt = null): Complaint
    {
        $complaint->loadMissing(['orderItem.order.buyer', 'orderItem.seller.user', 'orderItem.escrow']);

        $resolvedComplaint = DB::transaction(function () use ($complaint, $actor, $resolutionNote, $status, $orderStatus): Complaint {
            $complaint->forceFill([
                'status'          => $status,
                'resolved_by'     => $actor->id,
                'resolution_note' => $resolutionNote,
                'resolved_at'     => Carbon::parse($resolvedAt ?? now()),
            ])->save();

            $complaint->orderItem?->forceFill([
                'status' => $orderStatus,
            ])->save();

            $complaint->orderItem?->escrow?->forceFill([
                'status' => $status === ComplaintStatus::ApprovedRefund ? EscrowStatus::Refunded : EscrowStatus::Released,
            ])->save();

            return $complaint;
        });

        $this->notifyActivity(
            $resolvedComplaint,
            $actor,
            $status === ComplaintStatus::ApprovedRefund ? 'Complaint approved' : 'Complaint rejected',
            ($actor->username ?? 'Admin').' resolved '.$this->complaintSummary($resolvedComplaint).' as '.($status === ComplaintStatus::ApprovedRefund ? 'refund approved' : 'funds released').'.'
        );

        return $resolvedComplaint;
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
