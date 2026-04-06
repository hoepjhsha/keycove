<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * WithdrawStatus enum.
 *
 * Lifecycle status for withdrawal requests.
 *
 * Cases:
 * - Pending
 * - Processing
 * - Completed
 * - Cancelled
 * - Failed
 */
enum WithdrawStatus: int
{
    use HasLabels;

    case Pending = 0;
    case Processing = 1;
    case Completed = 2;
    case Cancelled = 3;
    case Failed = 4;
}
