<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Shop\PendingOrderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-pending-orders')]
#[Description('Cancel pending orders older than 24 hours and release reserved keys')]
class ExpirePendingOrders extends Command
{
    public function handle(PendingOrderService $pendingOrderService): int
    {
        $expiredAt = now()->subDay();
        $expiredCount = 0;

        Order::query()
            ->where('payment_status', PaymentStatus::Pending)
            ->where('created_at', '<=', $expiredAt)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$expiredCount, $pendingOrderService): void {
                foreach ($orders as $order) {
                    $pendingOrderService->cancel($order);
                    $expiredCount++;
                }
            });

        $this->info(sprintf('Expired %d pending order(s).', $expiredCount));

        return self::SUCCESS;
    }
}
