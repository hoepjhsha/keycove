<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * GeneralStatus enum.
 *
 * Generic status used for multiple models (category, region, etc.).
 *
 * Cases:
 * - Inactive: Not visible/inactive
 * - Active: Visible/active
 * - Hidden: Hidden from listings
 * - Deleted: Soft-deleted
 */
enum GeneralStatus: int
{
    use HasLabels;

    case Inactive = 0;
    case Active = 1;
    case Hidden = 2;
    case Deleted = 3;
}
