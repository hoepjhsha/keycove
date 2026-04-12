<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'key' => 'site_name',
                'value' => 'KeyCove',
                'description' => 'The name of the website displayed everywhere',
            ],
            [
                'key' => 'site_description',
                'value' => 'Smart Digital Product Trading Platform - Buy and sell game keys, software licenses, and digital services securely',
                'description' => 'Meta description for SEO',
            ],
            [
                'key' => 'commission_rate',
                'value' => '10',
                'description' => 'Default platform commission rate (percentage) charged on each sale',
            ],
            [
                'key' => 'escrow_duration_hours',
                'value' => '48',
                'description' => 'Number of hours funds are held in escrow before release to seller',
            ],
            [
                'key' => 'kyc_required_for_sellers',
                'value' => 'true',
                'description' => 'Whether KYC verification is required before a user can become a seller',
            ],
            [
                'key' => 'dispute_response_deadline_hours',
                'value' => '72',
                'description' => 'Number of hours seller has to respond to a dispute before auto-refund',
            ],
            [
                'key' => 'max_keys_per_listing',
                'value' => '1000',
                'description' => 'Maximum number of keys allowed in a single listing',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'description' => 'Enable/disable maintenance mode for the platform',
            ],
            [
                'key' => 'allowed_payment_methods',
                'value' => json_encode(['VNPay', 'Stripe'], JSON_THROW_ON_ERROR),
                'description' => 'JSON array of enabled payment methods',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@keycove.com',
                'description' => 'Email address for customer support inquiries',
            ],
            [
                'key' => 'terms_of_service_url',
                'value' => '/terms',
                'description' => 'URL to the Terms of Service page',
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::firstOrCreate(
                ['key' => $config['key']],
                [
                    'value' => $config['value'],
                    'description' => $config['description'],
                ]
            );
        }
    }
}
