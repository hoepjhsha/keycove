<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KycStatus;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shopTypes = ['Games', 'Keys', 'Store', 'Gaming', 'Digital', 'Hub'];
        $shopName = fake()->company().' '.fake()->randomElement($shopTypes);

        return [
            'user_id' => User::factory()->seller(),
            'shop_name' => $shopName,
            'cccd_number' => Crypt::encryptString(fake()->numerify('############')),
            'kyc_status' => KycStatus::Approved,
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            $sellerUser = $user ?? User::factory()->seller()->create();

            return [
                'user_id' => $sellerUser->id,
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'kyc_status' => KycStatus::Pending,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'kyc_status' => KycStatus::Approved,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'kyc_status' => KycStatus::Rejected,
        ]);
    }
}
