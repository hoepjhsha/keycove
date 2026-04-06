<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * ProductListingStatus enum.
 *
 * Status of a seller's listing.
 *
 * Cases:
 * - Draft
 * - Pending
 * - Active
 * - Hidden
 * - Rejected
 * - Closed
 * - Deleted
 */
enum ProductListingStatus: int
{
    use HasLabels;

    case Draft = 0;
    case Pending = 1;
    case Active = 2;
    case Hidden = 3;
    case Rejected = 4;
    case Closed = 5;
    case Deleted = 6;
}
