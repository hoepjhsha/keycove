<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DisputeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDisputes();
    }

    protected function seedDisputes(): void
    {
        $eligibleOrderItems = OrderItem::with(['order', 'seller.user'])
            ->whereIn('status', [OrderStatus::Disputing, OrderStatus::Refunded])
            ->whereHas('order')
            ->get()
            ->shuffle();

        if ($eligibleOrderItems->isEmpty()) {
            return;
        }

        $administrators = User::whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])->get();

        $complaintConfigs = [
            ['status' => ComplaintStatus::Open, 'count' => 3],
            ['status' => ComplaintStatus::InProcess, 'count' => 3],
            ['status' => ComplaintStatus::Escalated, 'count' => 2],
            ['status' => ComplaintStatus::ApprovedRefund, 'count' => 2],
        ];

        $buyerMessages = [
            'Key này không hoạt động, shop kiểm tra giúp mình với.',
            'Mã đã báo used, mình cần đổi key hoặc hoàn tiền.',
            'Sản phẩm nhận được không đúng mô tả, nhờ hỗ trợ.',
            'Mình đã thử nhiều lần nhưng vẫn không kích hoạt được.',
        ];

        $sellerMessages = [
            'Shop đã nhận được phản ánh, mình kiểm tra key ngay.',
            'Bạn gửi giúp mình ảnh lỗi để đối soát nhé.',
            'Mình sẽ gửi key thay thế hoặc hỗ trợ refund theo tình trạng đơn.',
        ];

        $adminMessages = [
            'Chúng tôi đã tiếp nhận khiếu nại và đang đối soát bằng chứng.',
            'Vui lòng bổ sung ảnh lỗi và thông tin đơn hàng để xử lý tiếp.',
            'Sau khi xác minh, hệ thống sẽ cập nhật kết quả cuối cùng cho bạn.',
        ];

        foreach ($complaintConfigs as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $orderItem = $eligibleOrderItems->shift();

                if (! $orderItem) {
                    return;
                }

                if (Complaint::where('order_item_id', $orderItem->id)->exists()) {
                    continue;
                }

                $order = $orderItem->order;
                if (! $order) {
                    continue;
                }

                $complaintDate = Carbon::parse($order->created_at)->addDays(random_int(1, 4));

                $complaint = Complaint::create([
                    'order_item_id' => $orderItem->id,
                    'reason'        => fake()->randomElement([
                        'Product key is invalid or already used',
                        'Key does not match the product description',
                        'Received wrong region key',
                        'Key activation failed multiple times',
                    ]),
                    'evidence' => fake()->optional(0.7)->passthrough([
                        fake()->imageUrl(1200, 900, 'error'),
                        'Screenshot showing activation issue',
                    ]),
                    'status'      => $config['status'],
                    'resolved_by' => $config['status'] === ComplaintStatus::ApprovedRefund && $administrators->isNotEmpty()
                        ? $administrators->random()->id
                        : null,
                    'resolution_note' => $config['status'] === ComplaintStatus::ApprovedRefund
                        ? 'Refund approved after verifying key mismatch.'
                        : null,
                    'resolved_at' => $config['status'] === ComplaintStatus::ApprovedRefund
                        ? $complaintDate->copy()->addDays(random_int(1, 3))
                        : null,
                ]);

                $complaint->forceFill([
                    'created_at' => $complaintDate,
                    'updated_at' => $complaintDate,
                ])->saveQuietly();

                ComplaintMessage::create([
                    'complaint_id' => $complaint->id,
                    'sender_id'    => $order->buyer_id,
                    'message'      => fake()->randomElement($buyerMessages),
                    'attachments'  => fake()->optional(0.4)->passthrough([
                        fake()->imageUrl(1200, 900, 'screenshot'),
                    ]),
                ])->forceFill([
                    'created_at' => $complaintDate->copy()->addHours(random_int(1, 6)),
                    'updated_at' => $complaintDate->copy()->addHours(random_int(1, 6)),
                ])->saveQuietly();

                $sellerUser = $orderItem->seller?->user;
                if ($sellerUser) {
                    ComplaintMessage::create([
                        'complaint_id' => $complaint->id,
                        'sender_id'    => $sellerUser->id,
                        'message'      => fake()->randomElement($sellerMessages),
                        'attachments'  => [],
                    ])->forceFill([
                        'created_at' => $complaintDate->copy()->addHours(random_int(6, 24)),
                        'updated_at' => $complaintDate->copy()->addHours(random_int(6, 24)),
                    ])->saveQuietly();
                }

                if (in_array($config['status'], [ComplaintStatus::Escalated, ComplaintStatus::ApprovedRefund], true) && $administrators->isNotEmpty()) {
                    ComplaintMessage::create([
                        'complaint_id' => $complaint->id,
                        'sender_id'    => $administrators->random()->id,
                        'message'      => fake()->randomElement($adminMessages),
                        'attachments'  => [],
                    ])->forceFill([
                        'created_at' => $complaintDate->copy()->addDays(random_int(1, 4)),
                        'updated_at' => $complaintDate->copy()->addDays(random_int(1, 4)),
                    ])->saveQuietly();
                }
            }
        }
    }
}
