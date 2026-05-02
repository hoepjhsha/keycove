<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Shop\PaymentSettlementService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VNPayGateway implements PaymentGatewayInterface
{
    protected string $tmnCode;

    protected string $hashSecret;

    protected string $url;

    protected string $apiUrl;

    protected string $returnUrl;

    protected bool $refundMock;

    protected bool $withdrawMock;

    protected string $version;

    public function __construct()
    {
        $this->tmnCode = config('services.payment.vnpay.tmn_code', '');
        $this->hashSecret = config('services.payment.vnpay.hash_secret', '');
        $this->url = config('services.payment.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->apiUrl = config('services.payment.vnpay.api_url', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction');
        $this->returnUrl = config('services.payment.vnpay.return_url', '');

        $this->refundMock = config('services.payment.vnpay.refund_mock', false);
        $this->withdrawMock = config('services.payment.vnpay.withdraw_mock', false);

        $this->version = config('services.payment.vnpay.version', '2.1.0');
    }

    public function createPayment(array $data): string
    {
        $vnp_TxnRef = $data['txn_ref'] ?? random_int(1, 100000);
        $time = time();

        $inputData = [
            'vnp_Version'    => $this->version,
            'vnp_TmnCode'    => $this->tmnCode,
            'vnp_Amount'     => ($data['amount'] ?? 0) * 100,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => date('YmdHis', $time),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => request()->ip(),
            'vnp_Locale'     => $data['locale'] ?? 'vn',
            'vnp_OrderInfo'  => $data['order_info'] ?? 'Trans. Payment '.$vnp_TxnRef,
            'vnp_OrderType'  => $data['order_type'] ?? 'other',
            'vnp_ReturnUrl'  => $this->returnUrl,
            'vnp_TxnRef'     => $vnp_TxnRef,
            'vnp_ExpireDate' => date('YmdHis', $time + 900),
        ];

        if (! empty($data['bank_code'])) {
            $inputData['vnp_BankCode'] = $data['bank_code'];
        }

        ksort($inputData);

        $queryString = http_build_query($inputData);

        $vnp_Url = $this->url.'?'.$queryString;

        if (! empty($this->hashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $queryString, $this->hashSecret);
            $vnp_Url .= '&vnp_SecureHash='.$vnpSecureHash;
        }

        return $vnp_Url;
    }

    public function handleReturn(array $requestData): array
    {
        $isValid = $this->isValidSignature($requestData);
        $responseCode = $requestData['vnp_ResponseCode'] ?? null;

        return [
            'success'            => $isValid && $responseCode === '00',
            'is_valid_signature' => $isValid,
            'transaction_no'     => $requestData['vnp_TransactionNo'] ?? null,
            'amount'             => ($requestData['vnp_Amount'] ?? 0) / 100,
            'order_id'           => $requestData['vnp_TxnRef'] ?? null,
            'response_code'      => $responseCode,
            'bank_code'          => $requestData['vnp_BankCode'] ?? null,
            'order_info'         => $requestData['vnp_OrderInfo'] ?? null,
            'pay_date'           => $requestData['vnp_PayDate'] ?? null,
        ];
    }

    public function handleIpn(array $requestData): array
    {
        try {
            if (! $this->isValidSignature($requestData)) {
                return ['RspCode' => '97', 'Message' => 'Invalid signature'];
            }

            $orderId = $requestData['vnp_TxnRef'] ?? null;
            $vnp_Amount = ($requestData['vnp_Amount'] ?? 0) / 100;

            $order = Order::query()->where('order_code', $orderId)->first();

            if ($order === null) {
                return ['RspCode' => '01', 'Message' => 'Order not found'];
            }

            if ((float) $order->total_price !== (float) $vnp_Amount) {
                return ['RspCode' => '04', 'Message' => 'Invalid amount'];
            }

            app(PaymentSettlementService::class)->settlePaidOrder($order, $requestData);

            return ['RspCode' => '00', 'Message' => 'Confirm Success'];
        } catch (Exception $e) {
            Log::error('VNPay IPN Error: '.$e->getMessage());

            return ['RspCode' => '99', 'Message' => 'Unknown error'];
        }
    }

    public function refund(array $data): array
    {
        if ($this->refundMock) {
            return $this->refundMock($data);
        }

        return $this->refundLive($data);
    }

    public function withdraw(array $data): array
    {
        if ($this->withdrawMock) {
            return $this->withdrawMock($data);
        }

        return $this->withdrawLive($data);
    }

    protected function withdrawLive(array $data): array
    {
        try {
            $createDate = now()->format('YmdHis');
            $requestId = $data['request_id'] ?? ('WD'.$createDate.random_int(1000, 9999));

            $inputData = [
                'vnp_RequestId'  => $requestId,
                'vnp_Version'    => $this->version,
                'vnp_Command'    => 'withdraw',
                'vnp_TmnCode'    => $this->tmnCode,
                'vnp_TxnRef'     => $data['txn_ref'] ?? (string) random_int(100000, 999999),
                'vnp_Amount'     => (int) round(($data['amount'] ?? 0) * 100),
                'vnp_OrderInfo'  => $data['order_info'] ?? 'Withdraw transaction',
                'vnp_BankCode'   => $data['bank_code'] ?? '',
                'vnp_AccNo'      => $data['account_number'] ?? '',
                'vnp_AccName'    => $data['account_name'] ?? '',
                'vnp_CreateBy'   => $data['create_by'] ?? 'system',
                'vnp_CreateDate' => $createDate,
                'vnp_IpAddr'     => $data['ip_address'] ?? request()->ip(),
            ];

            $inputData = array_filter($inputData, fn ($value) => $value !== '');

            ksort($inputData);

            if ($this->hashSecret !== '') {
                $inputData['vnp_SecureHash'] = hash_hmac('sha512', http_build_query($inputData), $this->hashSecret);
            }

            $response = Http::asForm()
                ->timeout(30)
                ->retry(2, 200)
                ->post($this->apiUrl, $inputData);

            $payload = $response->json();

            if (! is_array($payload)) {
                return [
                    'success' => false,
                    'message' => 'Unexpected withdraw response from gateway.',
                    'payload' => $response->body(),
                ];
            }

            return [
                'success' => ($payload['RspCode'] ?? $payload['vnp_ResponseCode'] ?? null) === '00',
                'message' => $payload['Message'] ?? $payload['vnp_Message'] ?? 'Withdraw request handled.',
                'payload' => $payload,
            ];
        } catch (Exception $exception) {
            Log::error('VNPay Withdraw Error: '.$exception->getMessage());

            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    protected function withdrawMock(array $data): array
    {
        Log::info('VNPAY MOCK WITHDRAW called with data:', $data);

        $createDate = now()->format('YmdHis');
        $requestId = $data['request_id'] ?? ('WD'.$createDate.random_int(1000, 9999));
        $amount = $data['amount'] ?? 0;

        if ((int) $amount === 9999) {
            $errorPayload = [
                'vnp_ResponseId'   => $requestId,
                'vnp_Command'      => 'withdraw',
                'vnp_ResponseCode' => '91',
                'vnp_Message'      => 'Invalid bank account or insufficient balance (Mock Error)',
            ];

            return [
                'success' => false,
                'message' => $errorPayload['vnp_Message'],
                'payload' => $errorPayload,
            ];
        }

        $successPayload = [
            'vnp_ResponseId'        => $requestId,
            'vnp_Command'           => 'withdraw',
            'vnp_TmnCode'           => $this->tmnCode,
            'vnp_TxnRef'            => $data['txn_ref'] ?? (string) random_int(100000, 999999),
            'vnp_Amount'            => (int) round($amount * 100),
            'vnp_OrderInfo'         => $data['order_info'] ?? 'Withdraw transaction',
            'vnp_ResponseCode'      => '00',
            'vnp_Message'           => 'Confirm Success',
            'vnp_BankCode'          => $data['bank_code'] ?? 'NCB',
            'vnp_AccNo'             => $data['account_number'] ?? '123456789',
            'vnp_PayDate'           => $createDate,
            'vnp_TransactionNo'     => $data['transaction_no'] ?? (string) random_int(10000000, 99999999),
            'vnp_TransactionStatus' => '00',
        ];

        ksort($successPayload);
        if ($this->hashSecret !== '') {
            $successPayload['vnp_SecureHash'] = hash_hmac('sha512', http_build_query($successPayload), $this->hashSecret);
        }

        return [
            'success' => true,
            'message' => 'Withdraw request handled (MOCKED).',
            'payload' => $successPayload,
        ];
    }

    protected function refundLive(array $data): array
    {
        try {
            $createDate = now()->format('YmdHis');
            $requestId = $data['request_id'] ?? ('RF'.$createDate.random_int(1000, 9999));

            $inputData = [
                'vnp_RequestId'       => $requestId,
                'vnp_Version'         => $this->version,
                'vnp_Command'         => 'refund',
                'vnp_TmnCode'         => $this->tmnCode,
                'vnp_TransactionType' => '02',
                'vnp_TxnRef'          => $data['txn_ref'] ?? '',
                'vnp_Amount'          => (int) round(($data['amount'] ?? 0) * 100),
                'vnp_OrderInfo'       => $data['order_info'] ?? 'Refund transaction',
                'vnp_TransactionNo'   => $data['transaction_no'] ?? '',
                'vnp_TransactionDate' => $data['transaction_date'] ?? '',
                'vnp_CreateBy'        => $data['create_by'] ?? 'system',
                'vnp_CreateDate'      => $createDate,
                'vnp_IpAddr'          => $data['ip_address'] ?? request()->ip(),
            ];

            ksort($inputData);

            if ($this->hashSecret !== '') {
                $inputData['vnp_SecureHash'] = hash_hmac('sha512', http_build_query($inputData), $this->hashSecret);
            }

            $response = Http::asForm()
                ->timeout(30)
                ->retry(2, 200)
                ->post($this->apiUrl, $inputData);

            $payload = $response->json();

            if (! is_array($payload)) {
                return [
                    'success' => false,
                    'message' => 'Unexpected refund response from gateway.',
                    'payload' => $response->body(),
                ];
            }

            return [
                'success' => ($payload['RspCode'] ?? $payload['vnp_ResponseCode'] ?? null) === '00',
                'message' => $payload['Message'] ?? $payload['vnp_Message'] ?? 'Refund request handled.',
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

    protected function refundMock(array $data): array
    {
        Log::info('VNPAY MOCK REFUND called with data:', $data);

        $createDate = now()->format('YmdHis');
        $requestId = $data['request_id'] ?? ('RF'.$createDate.random_int(1000, 9999));
        $amount = $data['amount'] ?? 0;

        if ((int) $amount === 9999) {
            $errorPayload = [
                'vnp_ResponseId'   => $requestId,
                'vnp_Command'      => 'refund',
                'vnp_ResponseCode' => '91',
                'vnp_Message'      => 'Transaction not found (Mock Error)',
            ];

            return [
                'success' => false,
                'message' => $errorPayload['vnp_Message'],
                'payload' => $errorPayload,
            ];
        }

        $successPayload = [
            'vnp_ResponseId'        => $requestId,
            'vnp_Command'           => 'refund',
            'vnp_TmnCode'           => $this->tmnCode,
            'vnp_TxnRef'            => $data['txn_ref'] ?? '',
            'vnp_Amount'            => (int) round($amount * 100),
            'vnp_OrderInfo'         => $data['order_info'] ?? 'Refund transaction',
            'vnp_ResponseCode'      => '00',
            'vnp_Message'           => 'Confirm Success',
            'vnp_BankCode'          => 'NCB',
            'vnp_PayDate'           => $createDate,
            'vnp_TransactionNo'     => $data['transaction_no'] ?? (string) random_int(10000000, 99999999),
            'vnp_TransactionType'   => '02',
            'vnp_TransactionStatus' => '05',
        ];

        ksort($successPayload);
        if ($this->hashSecret !== '') {
            $successPayload['vnp_SecureHash'] = hash_hmac('sha512', http_build_query($successPayload), $this->hashSecret);
        }

        return [
            'success' => true,
            'message' => 'Refund request handled (MOCKED).',
            'payload' => $successPayload,
        ];
    }

    protected function isValidSignature(array $requestData): bool
    {
        $vnp_SecureHash = $requestData['vnp_SecureHash'] ?? '';
        unset($requestData['vnp_SecureHash'], $requestData['vnp_SecureHashType']);

        $inputData = array_filter($requestData, static function ($key) {
            return str_starts_with($key, 'vnp_');
        }, ARRAY_FILTER_USE_KEY);

        ksort($inputData);
        $secureHash = hash_hmac('sha512', http_build_query($inputData), $this->hashSecret);

        return hash_equals($secureHash, $vnp_SecureHash);
    }
}
