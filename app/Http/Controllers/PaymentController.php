<?php

namespace App\Http\Controllers;

use App\Abstracts\Controller;
use App\Managers\PaymentManager;
use App\Models\Order;
use App\Services\Shop\PaymentSettlementService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentManager $paymentManager;

    protected PaymentSettlementService $paymentSettlementService;

    public function __construct(PaymentManager $paymentManager, PaymentSettlementService $paymentSettlementService)
    {
        $this->paymentManager = $paymentManager;
        $this->paymentSettlementService = $paymentSettlementService;
    }

    /**
     * Handle the return from VNPAY after payment is completed.
     */
    public function vnpayReturn(Request $request): Factory|View|\Illuminate\View\View
    {
        $result = $this->paymentManager->driver('vnpay')->handleReturn($request->all());

        if ($result['success']) {
            $order = Order::query()->where('order_code', $result['order_id'])->first();

            if ($order !== null) {
                $this->paymentSettlementService->settlePaidOrder($order, $request->all());
            }
        }

        return view('payments.return', [
            'success'       => $result['success'],
            'txnRef'        => $result['order_id'],
            'amount'        => $result['amount'],
            'orderInfo'     => $result['order_info'],
            'responseCode'  => $result['response_code'],
            'transactionNo' => $result['transaction_no'],
            'bankCode'      => $result['bank_code'],
            'payDate'       => $result['pay_date'],
        ]);
    }

    /**
     * Handle the IPN (Instant Payment Notification) from VNPAY to update order status.
     */
    public function vnpayIpn(Request $request): JsonResponse
    {
        $result = $this->paymentManager->driver('vnpay')->handleIpn($request->all());

        return response()->json($result);
    }
}
