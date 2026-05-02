<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * OrderStatus enum.
 *
 * Lifecycle status for orders.
 *
 * Cases:
 * - PendingPayment: Waiting for payment
 * - Processing: Being processed
 * - Delivered: Delivered to buyer
 * - Disputing: In dispute
 * - Completed: Completed successfully
 * - Cancelled: Cancelled
 * - Refunded: Refunded
 */
enum OrderStatus: int
{
    use HasLabels;

    case PendingPayment = 0;
    case Processing = 1;
    case Delivered = 2;
    case Disputing = 3;
    case Completed = 4;
    case Cancelled = 5;
    case Refunded = 6;
}
