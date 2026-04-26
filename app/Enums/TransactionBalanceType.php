<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum TransactionBalanceType: int
{
    use HasLabels;

    case Available = 0;
    case Holding = 1;
    case WithdrawPending = 2;
}
