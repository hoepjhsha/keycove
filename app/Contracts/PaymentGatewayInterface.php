<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a payment request URL
     *
     * @param  array  $data  Data for payment (amount, txn_ref, order_info, etc.)
     */
    public function createPayment(array $data): string;

    /**
     * Handle the return request from the payment gateway
     *
     * @param  array  $requestData  Variables returned via GET
     */
    public function handleReturn(array $requestData): array;

    /**
     * Handle the Server-to-Server IPN (Webhook) request from the payment gateway
     *
     * @param  array  $requestData  Variables returned via GET/POST
     */
    public function handleIpn(array $requestData): array;

    /**
     * Process a refund request.
     *
     * @param  array  $data  Refund payload.
     */
    public function refund(array $data): array;
}
