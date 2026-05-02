<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * KycStatus enum.
 *
 * KYC verification status for sellers.
 *
 * Cases:
 * - Pending: Verification pending
 * - Approved: KYC approved
 * - Rejected: KYC rejected
 */
enum KycStatus: int
{
    use HasLabels;

    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;
}
