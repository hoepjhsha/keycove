<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * UserRole enum.
 *
 * Represents roles assigned to a user in the system.
 *
 * Cases:
 * - User: Regular user / buyer
 * - Seller: Seller account
 * - Admin: Administrator with elevated privileges
 * - SuperAdmin: Super administrator with full access
 */
enum UserRole: int
{
    use HasLabels;

    case User = 0;
    case Seller = 1;
    case Admin = 2;
    case SuperAdmin = 3;
}
