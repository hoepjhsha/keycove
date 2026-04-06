<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * TransactionType enum.
 *
 * Type of transaction.
 *
 * Cases:
 * - Withdraw: Money withdrawn from wallet
 * - Pay: Payment for order
 */
enum TransactionType: int
{
    use HasLabels;

    case Withdraw = 0;
    case Pay = 1;
}
