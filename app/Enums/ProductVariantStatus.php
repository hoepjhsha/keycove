<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * ProductVariantStatus enum.
 *
 * Status of a product variant.
 *
 * Cases:
 * - Draft
 * - Active
 * - Hidden
 * - Discontinued
 * - Deleted
 */
enum ProductVariantStatus: int
{
    use HasLabels;

    case Draft = 0;
    case Active = 1;
    case Hidden = 2;
    case Discontinued = 3;
    case Deleted = 4;
}
