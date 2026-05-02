<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum InternalWalletDirection: int
{
    use HasLabels;

    case Inflow = 0;
    case Outflow = 1;
    case Neutral = 2;
}
