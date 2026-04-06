<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemConfig>
 */
class SystemConfigFactory extends Factory
{
    protected $model = SystemConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $configs = [
            ['key' => 'site_name', 'value' => 'Keycove', 'description' => 'Website name displayed across the platform'],
            ['key' => 'site_description', 'value' => 'Buy and sell digital game keys', 'description' => 'Site meta description'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'description' => 'Enable/disable maintenance mode'],
            ['key' => 'commission_rate', 'value' => '5.0', 'description' => 'Platform commission rate in percentage'],
            ['key' => 'min_withdrawal_amount', 'value' => '50.00', 'description' => 'Minimum amount for seller withdrawals'],
            ['key' => 'max_withdrawal_amount', 'value' => '10000.00', 'description' => 'Maximum amount for single withdrawal'],
            ['key' => 'escrow_release_days', 'value' => '7', 'description' => 'Days before escrow is automatically released'],
            ['key' => 'max_keys_per_listing', 'value' => '1000', 'description' => 'Maximum product keys per listing'],
            ['key' => 'support_email', 'value' => 'support@keycove.com', 'description' => 'Customer support email address'],
            ['key' => 'kyc_required', 'value' => 'true', 'description' => 'Require KYC verification for sellers'],
            ['key' => 'registration_enabled', 'value' => 'true', 'description' => 'Allow new user registrations'],
            ['key' => 'featured_products_count', 'value' => '12', 'description' => 'Number of featured products on homepage'],
            ['key' => 'currency', 'value' => 'USD', 'description' => 'Default platform currency'],
            ['key' => 'tax_rate', 'value' => '0.0', 'description' => 'Default tax rate percentage'],
        ];

        $config = fake()->randomElement($configs);

        return [
            'key' => $config['key'],
            'value' => $config['value'],
            'description' => $config['description'],
        ];
    }

    public function maintenanceMode(bool $enabled = false): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'maintenance_mode',
            'value' => $enabled ? 'true' : 'false',
            'description' => 'Enable/disable maintenance mode',
        ]);
    }

    public function commissionRate(float $rate = 5.0): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'commission_rate',
            'value' => (string) $rate,
            'description' => 'Platform commission rate in percentage',
        ]);
    }

    public function escrowDays(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'escrow_release_days',
            'value' => (string) $days,
            'description' => 'Days before escrow is automatically released',
        ]);
    }
}
