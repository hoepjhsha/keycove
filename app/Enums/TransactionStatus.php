<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * TransactionStatus enum.
 *
 * Status of a payment or wallet transaction.
 *
 * Cases:
 * - Pending
 * - Completed
 * - Failed
 * - Cancelled
 */
enum TransactionStatus: int
{
    use HasLabels;

    case Pending = 0;
    case Completed = 1;
    case Failed = 2;
    case Cancelled = 3;
    case Voided = 4;
}
