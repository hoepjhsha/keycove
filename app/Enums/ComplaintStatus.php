<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * Enum representing the status of a complaint.
 *
 * Cases:
 * - Open: Complaint opened by a buyer
 * - InProcess: Complaint being investigated
 * - Escalated: Escalated to support/administration
 * - Resolved: Complaint resolved
 */
enum ComplaintStatus: int
{
    use HasLabels;

    case Open = 0;
    case InProcess = 1;
    case Escalated = 2;
    case Resolved = 3;
}
