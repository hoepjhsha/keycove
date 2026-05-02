<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasLabels;

/**
 * PaymentMethod enum.
 *
 * Supported payment gateways.
 *
 * Cases:
 * - VNPay: VNPay gateway
 * - Stripe: Stripe gateway
 */
enum PaymentMethod: int
{
    use HasLabels;

    case VNPay = 0;
    case Stripe = 1;
}
