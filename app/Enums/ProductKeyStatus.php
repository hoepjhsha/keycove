<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * ProductKeyStatus enum.
 *
 * Status of a product key.
 *
 * Cases:
 * - Available: Ready to be sold
 * - Reserved: Reserved/processing
 * - Sold: Already sold
 * - Refunded: Refunded after sale
 * - Disabled: Disabled/invalidated
 */
enum ProductKeyStatus: int
{
    use HasLabels;

    case Available = 0;
    case Reserved = 1;
    case Sold = 2;
    case Refunded = 3;
    case Disabled = 4;
}
