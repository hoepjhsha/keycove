<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * TransactionType enum.
 *
 * Type of transaction.
 */
enum TransactionType: int
{
    use HasLabels;

    case PaymentReceived = 0;
    case EscrowHold = 1;
    case EscrowRelease = 2;
    case Refund = 3;
    case WithdrawReserve = 4;
    case Withdraw = 5;
    case WithdrawRelease = 6;
    case Adjustment = 7;
}
