<?php

declare(strict_types=1);

return [
    'user_role' => [
        'User'       => 'User',
        'Seller'     => 'Seller',
        'Admin'      => 'Admin',
        'SuperAdmin' => 'Super Admin',
    ],

    'user_status' => [
        'Inactive' => 'Inactive',
        'Active'   => 'Active',
        'Blocked'  => 'Blocked',
        'Deleted'  => 'Deleted',
    ],

    'general_status' => [
        'Inactive' => 'Inactive',
        'Active'   => 'Active',
        'Hidden'   => 'Hidden',
        'Deleted'  => 'Deleted',
    ],

    'gender' => [
        'Unknown' => 'Unknown',
        'Male'    => 'Male',
        'Female'  => 'Female',
    ],

    'kyc_status' => [
        'Pending'  => 'Pending',
        'Approved' => 'Approved',
        'Rejected' => 'Rejected',
    ],

    'order_status' => [
        'PendingPayment' => 'Pending Payment',
        'Processing'     => 'Processing',
        'Delivered'      => 'Delivered',
        'Disputing'      => 'Disputing',
        'Completed'      => 'Completed',
        'Cancelled'      => 'Cancelled',
        'Refunded'       => 'Refunded',
    ],

    'payment_method' => [
        'VNPay'  => 'VNPay',
        'Stripe' => 'Stripe',
    ],

    'payment_status' => [
        'Pending'   => 'Pending',
        'Completed' => 'Completed',
        'Failed'    => 'Failed',
        'Cancelled' => 'Cancelled',
        'Refunded'  => 'Refunded',
    ],

    'product_key_status' => [
        'Available' => 'Available',
        'Reserved'  => 'Reserved',
        'Sold'      => 'Sold',
        'Refunded'  => 'Refunded',
        'Disabled'  => 'Disabled',
    ],

    'product_listing_status' => [
        'Draft'    => 'Draft',
        'Pending'  => 'Pending',
        'Active'   => 'Active',
        'Hidden'   => 'Hidden',
        'Rejected' => 'Rejected',
        'Closed'   => 'Closed',
        'Deleted'  => 'Deleted',
    ],

    'product_variant_status' => [
        'Draft'        => 'Draft',
        'Active'       => 'Active',
        'Hidden'       => 'Hidden',
        'Discontinued' => 'Discontinued',
        'Deleted'      => 'Deleted',
    ],

    'transaction_status' => [
        'Pending'   => 'Pending',
        'Completed' => 'Completed',
        'Failed'    => 'Failed',
        'Cancelled' => 'Cancelled',
        'Voided'    => 'Voided',
    ],

    'transaction_type' => [
        'PaymentReceived' => 'Payment Received',
        'EscrowHold'      => 'Escrow Hold',
        'EscrowRelease'   => 'Escrow Release',
        'Refund'          => 'Refund',
        'WithdrawReserve' => 'Withdraw Reserve',
        'Withdraw'        => 'Withdraw',
        'WithdrawRelease' => 'Withdraw Release',
        'Adjustment'      => 'Adjustment',
    ],

    'transaction_balance_type' => [
        'Available'       => 'Available',
        'Holding'         => 'Holding',
        'WithdrawPending' => 'Withdraw Pending',
    ],

    'wallet_type' => [
        'Seller'   => 'Seller',
        'Internal' => 'Internal',
    ],

    'internal_wallet_direction' => [
        'Inflow'  => 'Inflow',
        'Outflow' => 'Outflow',
        'Neutral' => 'Neutral',
    ],

    'internal_wallet_entry_type' => [
        'PaymentReceived'               => 'Payment Received',
        'EscrowHeld'                    => 'Escrow Held',
        'EscrowReleased'                => 'Escrow Released',
        'RefundPaid'                    => 'Refund Paid',
        'SellerPayoutRequested'         => 'Seller Payout Requested',
        'SellerPayoutCompleted'         => 'Seller Payout Completed',
        'SellerPayoutFailed'            => 'Seller Payout Failed',
        'Adjustment'                    => 'Adjustment',
        'PlatformProfitPayoutRequested' => 'Platform Profit Payout Requested',
        'PlatformProfitPayoutCompleted' => 'Platform Profit Payout Completed',
        'PlatformProfitPayoutFailed'    => 'Platform Profit Payout Failed',
    ],

    'platform_payout_status' => [
        'Pending'    => 'Pending',
        'Processing' => 'Processing',
        'Completed'  => 'Completed',
        'Failed'     => 'Failed',
        'Cancelled'  => 'Cancelled',
    ],

    'withdraw_status' => [
        'Pending'    => 'Pending',
        'Processing' => 'Processing',
        'Completed'  => 'Completed',
        'Rejected'   => 'Rejected',
        'Cancelled'  => 'Cancelled',
        'Failed'     => 'Failed',
    ],

    'complaint_status' => [
        'Open'            => 'Open',
        'InProcess'       => 'In Process',
        'Escalated'       => 'Escalated',
        'ApprovedRefund'  => 'Approved - Refund',
        'RejectedRelease' => 'Rejected - Release',
    ],

    'escrow_status' => [
        'Holding'  => 'Holding',
        'Released' => 'Released',
        'Refunded' => 'Refunded',
        'Frozen'   => 'Frozen',
    ],

    'audit_event' => [
        'EscrowReleased' => 'Released',
        'EscrowFrozen'   => 'Frozen',
        'EscrowUnfrozen' => 'Unfrozen',
        'EscrowExtended' => 'Extended',
        'EscrowVoided'   => 'Voided',
    ],
];
