<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

enum AuditEvent: string
{
    use HasLabels;

    case EscrowReleased = 'release';
    case EscrowFrozen = 'frozen';
    case EscrowExtended = 'extended';
    case EscrowVoided = 'voided';
}
