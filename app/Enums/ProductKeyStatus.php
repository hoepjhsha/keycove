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
 * - Pending: Reserved/processing
 * - Sold: Already sold
 * - Revoked: Revoked/invalidated
 */
enum ProductKeyStatus: int
{
    use HasLabels;

    case Available = 0;
    case Pending = 1;
    case Sold = 2;
    case Revoked = 3;
}
