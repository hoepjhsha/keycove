<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum PlatformPayoutStatus: int
{
    use HasLabels;

    case Pending = 0;
    case Processing = 1;
    case Completed = 2;
    case Failed = 3;
    case Cancelled = 4;
}
