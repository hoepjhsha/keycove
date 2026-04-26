<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum PaymentStatus: int
{
    use HasLabels;

    case Pending = 0;
    case Completed = 1;
    case Failed = 2;
    case Cancelled = 3;
    case Refunded = 4;
}
