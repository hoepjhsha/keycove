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
        // Get order items that are disputing or refunded under the new per-item status model.
        $eligibleOrderItems = OrderItem::whereIn('status', [OrderStatus::Disputing, OrderStatus::Refunded])->get();

        $admins = User::where('role', UserRole::Admin)->get();
        $sellers = User::where('role', UserRole::Seller)->get();

        if ($eligibleOrderItems->isEmpty()) {
            return;
        }

        // Complaint distribution
        $complaintConfigs = [
            ['status' => ComplaintStatus::Open, 'count' => 3],
            ['status' => ComplaintStatus::InProcess, 'count' => 4],
            ['status' => ComplaintStatus::Escalated, 'count' => 2],
            ['status' => ComplaintStatus::ApprovedRefund, 'count' => 3],
        ];

        $reasons = [
            'Product key is invalid or already used',
            'Key does not match the product description',
            'Seller is not responding to messages',
            'Product delivered is different from what was ordered',
            'Key activation failed multiple times',
            'Received wrong region key',
            'Product missing promised DLC or content',
            'Key has been revoked after purchase',
        ];

        $buyerMessages = [
            'Hi, I purchased this key but it\'s not working. Can you help?',
            'The key shows as already activated. I need a refund please.',
            'I\'ve tried multiple times but the key keeps failing to activate.',
            'This key is for a different region than what was listed in the product.',
            'Seller hasn\'t responded to my messages for 3 days now.',
            'The key doesn\'t include the DLC that was promised in the description.',
            'I need urgent help with this order. The key is invalid.',
        ];

        $sellerResponses = [
            'Hi, I\'m looking into this issue now. Please give me a moment to check.',
            'Sorry for the inconvenience. Let me send you a replacement key.',
            'I\'ve checked and the key should work. Can you send a screenshot of the error?',
            'I apologize for the delay. I\'ll resolve this within 24 hours.',
            'Thank you for your patience. I\'m working with the supplier to fix this.',
            'I understand your frustration. Let me escalate this to the platform admin.',
        ];

        $adminMessages = [
            'We\'re reviewing your case and will respond within 48 hours.',
            'Please provide additional evidence (screenshots) to support your claim.',
            'After reviewing both parties\' evidence, we\'ve made a decision.',
            'We\'ve issued a full refund to the buyer. The seller\'s account has been noted.',
            'The dispute has been resolved in favor of the seller. Funds will be released.',
        ];

        $itemsUsed = collect();

        foreach ($complaintConfigs as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $availableItems = $eligibleOrderItems->diffKeys($itemsUsed);

                if ($availableItems->isEmpty()) {
                    break;
                }

                $orderItem = $availableItems->random();
                $itemsUsed->put($orderItem->id, $orderItem);

                // Skip if complaint already exists
                if (Complaint::where('order_item_id', $orderItem->id)->exists()) {
                    continue;
                }

                $order = $orderItem->order;
                if (! $order) {
                    continue;
                }

                $complaint = Complaint::create([
                    'order_item_id' => $orderItem->id,
                    'reason'        => fake()->randomElement($reasons),
                    'evidence'      => fake()->optional(0.7)->passthrough([
                        fake()->imageUrl(800, 600, 'error'),
                        'Screenshot showing activation error',
                    ]),
                    'status'     => $config['status'],
                    'created_at' => $order->created_at->copy()->addDays(rand(1, 3)),
                ]);

                // Create complaint messages
                $numMessages = rand(3, 6);

                // Buyer's initial message
                ComplaintMessage::create([
                    'complaint_id' => $complaint->id,
                    'sender_id'    => $order->buyer_id,
                    'message'      => fake()->randomElement($buyerMessages),
                    'attachments'  => fake()->optional(0.5)->passthrough([
                        fake()->imageUrl(800, 600, 'screenshot'),
                    ]),
                    'created_at' => $complaint->created_at->copy()->addHours(rand(1, 12)),
                ]);

                // Seller response (if in process or beyond)
                if (in_array($config['status'], [ComplaintStatus::InProcess, ComplaintStatus::Escalated, ComplaintStatus::ApprovedRefund], true)) {
                    if ($sellers->isNotEmpty()) {
                        ComplaintMessage::create([
                            'complaint_id' => $complaint->id,
                            'sender_id'    => $sellers->random()->id,
                            'message'      => fake()->randomElement($sellerResponses),
                            'attachments'  => [],
                            'created_at'   => $complaint->created_at->copy()->addHours(rand(12, 48)),
                        ]);
                    }
                }

                // Admin messages (if escalated or resolved)
                if (in_array($config['status'], [ComplaintStatus::Escalated, ComplaintStatus::ApprovedRefund], true) && $admins->isNotEmpty()) {
                    $numAdminMessages = rand(1, 2);

                    for ($j = 0; $j < $numAdminMessages; $j++) {
                        ComplaintMessage::create([
                            'complaint_id' => $complaint->id,
                            'sender_id'    => $admins->random()->id,
                            'message'      => fake()->randomElement($adminMessages),
                            'attachments'  => [],
                            'created_at'   => $complaint->created_at->copy()->addDays(rand(2, 5)),
                        ]);
                    }
                }

                // Additional buyer follow-ups
                if ($numMessages > 2) {
                    for ($k = 0; $k < $numMessages - 2; $k++) {
                        ComplaintMessage::create([
                            'complaint_id' => $complaint->id,
                            'sender_id'    => $order->buyer_id,
                            'message'      => fake()->randomElement($buyerMessages),
                            'attachments'  => fake()->optional(0.3)->passthrough([
                                fake()->imageUrl(800, 600, 'evidence'),
                            ]),
                            'created_at' => $complaint->created_at->copy()->addDays(rand(3, 7)),
                        ]);
                    }
                }
            }
        }
    }
}
