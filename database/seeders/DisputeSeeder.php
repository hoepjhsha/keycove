<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DisputeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::query()->whereIn('role', [UserRole::SuperAdmin, UserRole::Admin])->orderBy('id')->first();

        if ($adminUser === null) {
            return;
        }

        $items = OrderItem::query()
            ->with(['order.buyer', 'seller.user'])
            ->whereDoesntHave('complaint')
            ->whereIn('status', [OrderStatus::Disputing, OrderStatus::Refunded, OrderStatus::Completed])
            ->get();

        $priorityItems = $items->whereIn('status', [OrderStatus::Disputing, OrderStatus::Refunded])->values();
        $fallbackItems = $items->where('status', OrderStatus::Completed)->values();
        $targetCount = max(24, (int) floor(OrderItem::query()->count() * 0.02));
        $selectedItems = $priorityItems->shuffle()->take((int) min($priorityItems->count(), ceil($targetCount * 0.75)));

        if ($selectedItems->count() < $targetCount) {
            $selectedItems = $selectedItems->merge(
                $fallbackItems->shuffle()->take($targetCount - $selectedItems->count())
            );
        }

        foreach ($selectedItems as $orderItem) {
            $createdAt = Carbon::parse($orderItem->updated_at)->copy()->addHours(random_int(6, 72));
            $status = $this->resolveComplaintStatus($orderItem->status);
            $resolvedAt = in_array($status, [ComplaintStatus::ApprovedRefund, ComplaintStatus::RejectedRelease], true)
                ? $createdAt->copy()->addDays(random_int(1, 5))
                : null;

            $complaint = Complaint::create([
                'order_item_id'  => $orderItem->id,
                'resolved_by'    => $resolvedAt ? $adminUser->id : null,
                'complaint_code' => 'DSP-'.strtoupper(Str::random(10)),
                'reason'         => $this->reasonForItem($orderItem->product_name_snapshot),
                'evidence'       => [
                    'https://placehold.co/1280x720/png?text=Activation+Error',
                    'https://placehold.co/1280x720/png?text=Order+Chat+Log',
                ],
                'status'          => $status,
                'resolution_note' => $this->resolutionNote($status),
                'resolved_at'     => $resolvedAt,
                'created_at'      => $createdAt,
                'updated_at'      => $resolvedAt ?? $createdAt,
            ]);

            $this->seedMessages($complaint, $orderItem, $adminUser, $createdAt, $status);
        }
    }

    protected function resolveComplaintStatus(OrderStatus $orderStatus): ComplaintStatus
    {
        return match ($orderStatus) {
            OrderStatus::Refunded  => ComplaintStatus::ApprovedRefund,
            OrderStatus::Disputing => fake()->randomElement([
                ComplaintStatus::Open,
                ComplaintStatus::InProcess,
                ComplaintStatus::Escalated,
            ]),
            default => ComplaintStatus::RejectedRelease,
        };
    }

    protected function reasonForItem(string $productName): string
    {
        return fake()->randomElement([
            'Key kich hoat bao da duoc su dung truoc do cho '.$productName.'.',
            'Phien ban/region nhan duoc khong dung voi mo ta luc dat mua.',
            'Nguoi mua gap loi sau khi redeem va can kiem tra lai key da giao.',
            'Seller phan hoi cham khi buyer gui bang chung loi kich hoat.',
        ]);
    }

    protected function resolutionNote(ComplaintStatus $status): ?string
    {
        return match ($status) {
            ComplaintStatus::ApprovedRefund  => 'Da doi chieu bang chung va chap nhan hoan tien cho nguoi mua.',
            ComplaintStatus::RejectedRelease => 'Bang chung chua du co so, giao dich duoc giai phong ve seller.',
            default                          => null,
        };
    }

    protected function seedMessages(Complaint $complaint, OrderItem $orderItem, User $adminUser, Carbon $createdAt, ComplaintStatus $status): void
    {
        $participants = new Collection([
            $orderItem->order->buyer,
            $orderItem->seller?->user,
            $adminUser,
        ]);

        $messages = [
            ['sender_id' => $orderItem->order->buyer_id, 'message' => 'Minh da thu kich hoat nhieu lan nhung he thong bao loi, gui kem anh chup man hinh de doi soat.'],
            ['sender_id' => $orderItem->seller?->user_id ?? $adminUser->id, 'message' => 'Shop da kiem tra nguon key va dang doi chieu lai lich su giao key voi nha cung cap.'],
            ['sender_id' => $adminUser->id, 'message' => 'Ho tro KeyCove da tiep nhan vu viec, vui long bo sung them thong tin neu can.'],
        ];

        if ($status === ComplaintStatus::ApprovedRefund) {
            $messages[] = ['sender_id' => $adminUser->id, 'message' => 'Sau khi doi chieu bang chung, he thong da phe duyet hoan tien cho don hang nay.'];
        }

        if ($status === ComplaintStatus::RejectedRelease) {
            $messages[] = ['sender_id' => $adminUser->id, 'message' => 'Bang chung hien tai chua du co so de hoan tien. He thong se giai phong giao dich cho seller.'];
        }

        foreach ($messages as $index => $payload) {
            $messageCreatedAt = $createdAt->copy()->addHours(($index + 1) * random_int(2, 8));

            ComplaintMessage::create([
                'complaint_id' => $complaint->id,
                'sender_id'    => $payload['sender_id'],
                'message'      => $payload['message'],
                'attachments'  => $index === 0 ? ['https://placehold.co/1280x720/png?text=Buyer+Evidence'] : null,
                'created_at'   => $messageCreatedAt,
                'updated_at'   => $messageCreatedAt,
            ]);
        }
    }
}
