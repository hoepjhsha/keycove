<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VNPayGateway implements PaymentGatewayInterface
{
    protected string $tmnCode;

    protected string $hashSecret;

    protected string $url;

    protected string $returnUrl;

    protected string $refundUrl;

    public function __construct()
    {
        $this->tmnCode = config('services.vnpay.tmn_code', '');
        $this->hashSecret = config('services.vnpay.hash_secret', '');
        $this->url = config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->returnUrl = config('services.vnpay.return_url', url('/payment/vnpay/return'));
        $this->refundUrl = config('services.vnpay.refund_url', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction');
    }

    public function createPayment(array $data): string
    {
        $vnp_TxnRef = $data['txn_ref'] ?? random_int(1, 10000);
        $vnp_Amount = $data['amount'];
        $vnp_Locale = $data['locale'] ?? 'vn';
        $vnp_BankCode = $data['bank_code'] ?? '';
        $vnp_IpAddr = request()->ip();

        $startTime = date('YmdHis');
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $this->tmnCode,
            'vnp_Amount'     => $vnp_Amount * 100,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $vnp_IpAddr,
            'vnp_Locale'     => $vnp_Locale,
            'vnp_OrderInfo'  => $data['order_info'] ?? 'Thanh toan GD: '.$vnp_TxnRef,
            'vnp_OrderType'  => $data['order_type'] ?? 'other',
            'vnp_ReturnUrl'  => $this->returnUrl,
            'vnp_TxnRef'     => $vnp_TxnRef,
            'vnp_ExpireDate' => $expire,
        ];

        if (! empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashData = '';

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        $vnp_Url = $this->url.'?'.$query;
        if (! empty($this->hashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashData, $this->hashSecret);
            $vnp_Url .= 'vnp_SecureHash='.$vnpSecureHash;
        }

        return $vnp_Url;
    }

    public function handleReturn(array $requestData): array
    {
        $vnp_SecureHash = $requestData['vnp_SecureHash'] ?? '';
        unset($requestData['vnp_SecureHash'], $requestData['vnp_SecureHashType']);

        $inputData = [];
        foreach ($requestData as $key => $value) {
            if (substr($key, 0, 4) == 'vnp_') {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);
        $i = 0;
        $hashData = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData.'&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData = $hashData.urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        $isSuccess = ($secureHash === $vnp_SecureHash && ($inputData['vnp_ResponseCode'] ?? '') == '00');

        return [
            'success'            => $isSuccess,
            'is_valid_signature' => $secureHash === $vnp_SecureHash,
            'transaction_no'     => $inputData['vnp_TransactionNo'] ?? null,
            'amount'             => ($inputData['vnp_Amount'] ?? 0) / 100,
            'order_id'           => $inputData['vnp_TxnRef'] ?? null,
            'response_code'      => $inputData['vnp_ResponseCode'] ?? null,
            'bank_code'          => $inputData['vnp_BankCode'] ?? null,
            'order_info'         => $inputData['vnp_OrderInfo'] ?? null,
            'pay_date'           => $inputData['vnp_PayDate'] ?? null,
        ];
    }

    public function handleIpn(array $requestData): array
    {
        try {
            $vnp_SecureHash = $requestData['vnp_SecureHash'] ?? '';
            unset($requestData['vnp_SecureHash']);
            unset($requestData['vnp_SecureHashType']);

            $inputData = [];
            foreach ($requestData as $key => $value) {
                if (substr($key, 0, 4) == 'vnp_') {
                    $inputData[$key] = $value;
                }
            }

            ksort($inputData);
            $i = 0;
            $hashData = '';
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData.'&'.urlencode($key).'='.urlencode($value);
                } else {
                    $hashData = $hashData.urlencode($key).'='.urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

            if ($secureHash !== $vnp_SecureHash) {
                return ['RspCode' => '97', 'Message' => 'Invalid signature'];
            }

            $orderId = $inputData['vnp_TxnRef'];
            $vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100;

            // TODO: Lấy đơn hàng từ DB theo $orderId ở đây
            $order = null; // Giả lập chưa query database
            // Giả lập lấy được order:
            // $order = Order::find($orderId);

            // Kiểm tra fake để return
            // Ở thực tế bạn sẽ check if (!$order) return '01'; check amount return '04'; check trạng thái return '02'
            // Đoạn này trả về thành công giả lập (nơi cập nhật trạng thái đơn hàng).

            /* Xử lý thật ở đây:
            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                $order->update(['status' => 'paid']);
            } else {
                $order->update(['status' => 'failed']);
            }
            */

            return ['RspCode' => '00', 'Message' => 'Confirm Success'];

        } catch (Exception $e) {
            Log::error('VNPay IPN Error: '.$e->getMessage());

            return ['RspCode' => '99', 'Message' => 'Unknown error'];
        }
    }

    public function refund(array $data): array
    {
        try {
            $requestId = $data['request_id'] ?? ('RF'.now()->format('YmdHis').random_int(1000, 9999));
            $inputData = [
                'vnp_RequestId'       => $requestId,
                'vnp_Version'         => '2.1.0',
                'vnp_Command'         => 'refund',
                'vnp_TmnCode'         => $this->tmnCode,
                'vnp_TransactionType' => '02',
                'vnp_TxnRef'          => $data['txn_ref'] ?? '',
                'vnp_Amount'          => (int) round(((float) ($data['amount'] ?? 0)) * 100),
                'vnp_OrderInfo'       => $data['order_info'] ?? 'Refund transaction',
                'vnp_TransactionNo'   => $data['transaction_no'] ?? '',
                'vnp_TransactionDate' => $data['transaction_date'] ?? '',
                'vnp_CreateBy'        => $data['create_by'] ?? 'system',
                'vnp_CreateDate'      => now()->format('YmdHis'),
                'vnp_IpAddr'          => $data['ip_address'] ?? request()->ip(),
            ];

            ksort($inputData);
            $hashData = '';
            $i = 0;

            foreach ($inputData as $key => $value) {
                if ($i === 1) {
                    $hashData .= '&'.urlencode($key).'='.urlencode((string) $value);
                } else {
                    $hashData .= urlencode($key).'='.urlencode((string) $value);
                    $i = 1;
                }
            }

            if ($this->hashSecret !== '') {
                $inputData['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $this->hashSecret);
            }

            $response = Http::asForm()
                ->timeout(30)
                ->retry(2, 200)
                ->post($this->refundUrl, $inputData);

            $payload = $response->json();

            if (! is_array($payload)) {
                return [
                    'success' => false,
                    'message' => 'Unexpected refund response from gateway.',
                    'payload' => $response->body(),
                ];
            }

            return [
                'success' => ($payload['RspCode'] ?? null) === '00',
                'message' => $payload['Message'] ?? 'Refund request handled.',
                'payload' => $payload,
            ];
        } catch (Exception $exception) {
            Log::error('VNPay Refund Error: '.$exception->getMessage());

            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
