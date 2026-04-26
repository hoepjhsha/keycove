<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum WalletType: int
{
    use HasLabels;

    case Seller = 0;
    case Internal = 1;
}
