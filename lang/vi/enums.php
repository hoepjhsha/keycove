<?php

declare(strict_types=1);

return [
    'user_role' => [
        'User'       => 'Người dùng',
        'Seller'     => 'Người bán',
        'Admin'      => 'Quản trị viên',
        'SuperAdmin' => 'Quản trị cấp cao',
    ],

    'user_status' => [
        'Inactive' => 'Chưa kích hoạt',
        'Active'   => 'Đang hoạt động',
        'Blocked'  => 'Bị chặn',
        'Deleted'  => 'Đã xóa',
    ],

    'general_status' => [
        'Inactive' => 'Chưa kích hoạt',
        'Active'   => 'Đang hoạt động',
        'Hidden'   => 'Đã ẩn',
        'Deleted'  => 'Đã xóa',
    ],

    'gender' => [
        'Unknown' => 'Không xác định',
        'Male'    => 'Nam',
        'Female'  => 'Nữ',
    ],

    'kyc_status' => [
        'Pending'  => 'Đang chờ duyệt',
        'Approved' => 'Đã duyệt',
        'Rejected' => 'Đã từ chối',
    ],

    'order_status' => [
        'PendingPayment' => 'Chờ thanh toán',
        'Processing'     => 'Đang xử lý',
        'Delivered'      => 'Đã giao',
        'Disputing'      => 'Đang khiếu nại',
        'Completed'      => 'Hoàn tất',
        'Cancelled'      => 'Đã hủy',
        'Refunded'       => 'Đã hoàn tiền',
    ],

    'payment_method' => [
        'VNPay'  => 'VNPay',
        'Stripe' => 'Stripe',
    ],

    'payment_status' => [
        'Pending'   => 'Đang chờ',
        'Completed' => 'Hoàn tất',
        'Failed'    => 'Thất bại',
        'Cancelled' => 'Đã hủy',
        'Refunded'  => 'Đã hoàn tiền',
    ],

    'product_key_status' => [
        'Available' => 'Có sẵn',
        'Reserved'  => 'Đã giữ chỗ',
        'Sold'      => 'Đã bán',
        'Refunded'  => 'Đã hoàn tiền',
        'Disabled'  => 'Đã vô hiệu',
    ],

    'product_listing_status' => [
        'Draft'    => 'Bản nháp',
        'Pending'  => 'Đang chờ',
        'Active'   => 'Đang bán',
        'Hidden'   => 'Đã ẩn',
        'Rejected' => 'Đã từ chối',
        'Closed'   => 'Đã đóng',
        'Deleted'  => 'Đã xóa',
    ],

    'product_variant_status' => [
        'Draft'        => 'Bản nháp',
        'Active'       => 'Đang hoạt động',
        'Hidden'       => 'Đã ẩn',
        'Discontinued' => 'Ngừng bán',
        'Deleted'      => 'Đã xóa',
    ],

    'transaction_status' => [
        'Pending'   => 'Đang chờ',
        'Completed' => 'Hoàn tất',
        'Failed'    => 'Thất bại',
        'Cancelled' => 'Đã hủy',
        'Voided'    => 'Đã hủy bỏ',
    ],

    'transaction_type' => [
        'PaymentReceived' => 'Đã nhận thanh toán',
        'EscrowHold'      => 'Giữ tiền ký quỹ',
        'EscrowRelease'   => 'Giải phóng ký quỹ',
        'Refund'          => 'Hoàn tiền',
        'WithdrawReserve' => 'Giữ tiền rút',
        'Withdraw'        => 'Rút tiền',
        'WithdrawRelease' => 'Giải phóng tiền rút',
        'Adjustment'      => 'Điều chỉnh',
    ],

    'transaction_balance_type' => [
        'Available'       => 'Khả dụng',
        'Holding'         => 'Đang giữ',
        'WithdrawPending' => 'Chờ rút',
    ],

    'wallet_type' => [
        'Seller'   => 'Người bán',
        'Internal' => 'Nội bộ',
    ],

    'internal_wallet_direction' => [
        'Inflow'  => 'Tiền vào',
        'Outflow' => 'Tiền ra',
        'Neutral' => 'Đối soát',
    ],

    'internal_wallet_entry_type' => [
        'PaymentReceived'               => 'Nhận thanh toán',
        'EscrowHeld'                    => 'Ghi nhận ký quỹ',
        'EscrowReleased'                => 'Giải ngân ký quỹ',
        'RefundPaid'                    => 'Hoàn tiền',
        'SellerPayoutRequested'         => 'Yêu cầu trả seller',
        'SellerPayoutCompleted'         => 'Đã trả seller',
        'SellerPayoutFailed'            => 'Trả seller thất bại',
        'Adjustment'                    => 'Điều chỉnh',
        'PlatformProfitPayoutRequested' => 'Yêu cầu payout lợi nhuận',
        'PlatformProfitPayoutCompleted' => 'Đã payout lợi nhuận',
        'PlatformProfitPayoutFailed'    => 'Payout lợi nhuận thất bại',
    ],

    'platform_payout_status' => [
        'Pending'    => 'Đang chờ',
        'Processing' => 'Đang xử lý',
        'Completed'  => 'Hoàn tất',
        'Failed'     => 'Thất bại',
        'Cancelled'  => 'Đã hủy',
    ],

    'withdraw_status' => [
        'Pending'    => 'Đang chờ',
        'Processing' => 'Đang xử lý',
        'Completed'  => 'Hoàn tất',
        'Rejected'   => 'Đã từ chối',
        'Cancelled'  => 'Đã hủy',
        'Failed'     => 'Thất bại',
    ],

    'complaint_status' => [
        'Open'            => 'Đang mở',
        'InProcess'       => 'Đang xử lý',
        'Escalated'       => 'Đã chuyển cấp',
        'ApprovedRefund'  => 'Duyệt hoàn tiền',
        'RejectedRelease' => 'Từ chối và giải ngân',
    ],

    'escrow_status' => [
        'Holding'  => 'Đang giữ',
        'Released' => 'Đã giải ngân',
        'Refunded' => 'Đã hoàn tiền',
        'Frozen'   => 'Đã đóng băng',
    ],

    'audit_event' => [
        'EscrowReleased' => 'Đã giải ngân',
        'EscrowFrozen'   => 'Đã đóng băng',
        'EscrowExtended' => 'Đã gia hạn',
        'EscrowVoided'   => 'Đã hủy bỏ',
    ],
];
