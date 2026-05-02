<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * Gender enum.
 *
 * Represents user gender options.
 *
 * Cases:
 * - Unknown: Not specified
 * - Male: Male
 * - Female: Female
 */
enum Gender: int
{
    use HasLabels;

    case Unknown = 0;
    case Male = 1;
    case Female = 2;
}
