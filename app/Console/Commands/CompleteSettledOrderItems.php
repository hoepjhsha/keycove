<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\SystemConfig;
use App\Services\Shop\OrderItemCompletionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:complete-settled-order-items {--days= : Days to wait before auto-completing delivered order items}')]
#[Description('Auto-complete delivered order items after the buyer response window closes')]
class CompleteSettledOrderItems extends Command
{
    public function handle(OrderItemCompletionService $orderItemCompletionService): int
    {
        $completionDays = max(1, (int) ($this->option('days') ?: $this->configuredDays()));
        $threshold = now()->subDays($completionDays);
        $completedCount = 0;

        OrderItem::query()
            ->where('status', OrderStatus::Delivered)
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', $threshold)
            ->whereDoesntHave('complaint')
            ->orderBy('id')
            ->chunkById(100, function ($orderItems) use ($orderItemCompletionService, &$completedCount): void {
                foreach ($orderItems as $orderItem) {
                    if ($orderItemCompletionService->complete($orderItem->id, 'auto_complete')) {
                        $completedCount++;
                    }
                }
            });

        $this->info(sprintf('Completed %d settled order item(s).', $completedCount));

        return self::SUCCESS;
    }

    protected function configuredDays(): int
    {
        return (int) (SystemConfig::query()->where('key', 'order_auto_complete_days')->value('value') ?? 7);
    }
}
