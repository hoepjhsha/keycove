<?php

declare(strict_types=1);

return [
    'user_role' => [
        'User' => 'User',
        'Seller' => 'Seller',
        'Admin' => 'Admin',
        'SuperAdmin' => 'Super Admin',
    ],

    'user_status' => [
        'Inactive' => 'Inactive',
        'Active' => 'Active',
        'Blocked' => 'Blocked',
        'Deleted' => 'Deleted',
    ],

    'general_status' => [
        'Inactive' => 'Inactive',
        'Active' => 'Active',
        'Hidden' => 'Hidden',
        'Deleted' => 'Deleted',
    ],

    'gender' => [
        'Unknown' => 'Unknown',
        'Male' => 'Male',
        'Female' => 'Female',
    ],

    'kyc_status' => [
        'Pending' => 'Pending',
        'Approved' => 'Approved',
        'Rejected' => 'Rejected',
    ],

    'order_status' => [
        'PendingPayment' => 'Pending Payment',
        'Processing' => 'Processing',
        'Delivered' => 'Delivered',
        'Disputing' => 'Disputing',
        'Completed' => 'Completed',
        'Cancelled' => 'Cancelled',
        'Refunded' => 'Refunded',
    ],

    'payment_method' => [
        'VNPay' => 'VNPay',
        'Stripe' => 'Stripe',
    ],

    'product_key_status' => [
        'Available' => 'Available',
        'Pending' => 'Pending',
        'Sold' => 'Sold',
        'Revoked' => 'Revoked',
    ],

    'product_listing_status' => [
        'Draft' => 'Draft',
        'Pending' => 'Pending',
        'Active' => 'Active',
        'Hidden' => 'Hidden',
        'Rejected' => 'Rejected',
        'Closed' => 'Closed',
        'Deleted' => 'Deleted',
    ],

    'product_variant_status' => [
        'Draft' => 'Draft',
        'Active' => 'Active',
        'Hidden' => 'Hidden',
        'Discontinued' => 'Discontinued',
        'Deleted' => 'Deleted',
    ],

    'transaction_status' => [
        'Pending' => 'Pending',
        'Completed' => 'Completed',
        'Failed' => 'Failed',
        'Cancelled' => 'Cancelled',
    ],

    'transaction_type' => [
        'Withdraw' => 'Withdraw',
        'Pay' => 'Pay',
        'Refund' => 'Refund',
    ],

    'withdraw_status' => [
        'Pending' => 'Pending',
        'Processing' => 'Processing',
        'Completed' => 'Completed',
        'Cancelled' => 'Cancelled',
        'Failed' => 'Failed',
    ],

    'complaint_status' => [
        'Open' => 'Open',
        'InProcess' => 'In Process',
        'Escalated' => 'Escalated',
        'Resolved' => 'Resolved',
    ],

    'escrow_status' => [
        'Holding' => 'Holding',
        'Released' => 'Released',
        'Refunded' => 'Refunded',
    ],
];
