<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * UserStatus enum.
 *
 * Represents lifecycle status of a user account.
 *
 * Cases:
 * - Inactive: Not active yet
 * - Active: Active account
 * - Blocked: Blocked/suspended
 * - Deleted: Deleted account
 */
enum UserStatus: int
{
    use HasLabels;

    case Inactive = 0;
    case Active = 1;
    case Blocked = 2;
    case Deleted = 3;
}
