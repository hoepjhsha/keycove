<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * EscrowStatus enum.
 *
 * Status of escrow holding funds for an order.
 *
 * Cases:
 * - Holding: Funds are held in escrow
 * - Released: Funds have been released to the seller
 * - Refunded: Funds returned to buyer
 */
enum EscrowStatus: int
{
    use HasLabels;

    case Holding = 0;
    case Released = 1;
    case Refunded = 2;
}
