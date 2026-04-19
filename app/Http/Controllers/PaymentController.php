<?php

namespace App\Http\Controllers;

use App\Abstracts\Controller;
use App\Managers\PaymentManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    //    public function pay(Request $request)
    //    {
    //        $paymentData = [
    //            'txn_ref' => uniqid('ORDER_'),
    //            'amount' => 50000,
    //            'order_info' => 'Thanh toan hoa don test',
    //            'order_type' => 'other',
    //            'locale' => 'vn',
    //        ];
    //
    //        $url = $this->paymentManager->driver('vnpay')->createPayment($paymentData);
    //
    //        return redirect()->away($url);
    //    }

    /**
     * Handle the return from VNPAY after payment is completed.
     */
    public function vnpayReturn(Request $request): Factory|View|\Illuminate\View\View
    {
        $result = $this->paymentManager->driver('vnpay')->handleReturn($request->all());

        return view('payments.return', [
            'success' => $result['success'],
            'txnRef' => $result['order_id'],
            'amount' => $result['amount'],
            'orderInfo' => $result['order_info'],
            'responseCode' => $result['response_code'],
            'transactionNo' => $result['transaction_no'],
            'bankCode' => $result['bank_code'],
            'payDate' => $result['pay_date'],
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
