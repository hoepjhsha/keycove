<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\PlatformPayout;
use App\Models\PlatformPayoutItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformPayoutItem>
 */
class PlatformPayoutItemFactory extends Factory
{
    protected $model = PlatformPayoutItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform_payout_id' => PlatformPayout::factory(),
            'order_item_id'      => OrderItem::factory(),
            'amount'             => fake()->randomFloat(2, 10, 500),
            'profit_type'        => fake()->randomElement(['platform_fee', 'platform_owned_sale']),
            'metadata'           => ['source' => 'factory'],
        ];
    }
}
