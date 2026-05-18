<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum InternalWalletEntryType: int
{
    use HasLabels;

    case PaymentReceived = 0;
    case EscrowHeld = 1;
    case EscrowReleased = 2;
    case RefundPaid = 3;
    case SellerPayoutRequested = 4;
    case SellerPayoutCompleted = 5;
    case SellerPayoutFailed = 6;
    case Adjustment = 7;
    case PlatformProfitPayoutRequested = 8;
    case PlatformProfitPayoutCompleted = 9;
    case PlatformProfitPayoutFailed = 10;
}
