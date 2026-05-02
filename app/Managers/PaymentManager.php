<?php

namespace App\Managers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\VNPayGateway;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    /**
     * Create VNPay Driver.
     */
    protected function createVnpayDriver(): PaymentGatewayInterface
    {
        return new VNPayGateway;
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('services.payment.default', 'vnpay');
    }
}
