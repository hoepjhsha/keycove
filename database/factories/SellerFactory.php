<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KycStatus;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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

        $frontImage = 'sellers/kyc/front_'.fake()->uuid().'.jpg';
        $backImage = 'sellers/kyc/back_'.fake()->uuid().'.jpg';

        $disk = config('filesystems.default');
        Storage::disk($disk)->makeDirectory('sellers/kyc');
        Storage::disk($disk)->put($frontImage, file_get_contents('https://placehold.co/800x500/EEE/31343C/png?text=Front+ID+Card'));
        Storage::disk($disk)->put($backImage, file_get_contents('https://placehold.co/800x500/EEE/31343C/png?text=Back+ID+Card'));

        return [
            'user_id'          => User::factory()->seller(),
            'shop_name'        => $shopName,
            'cccd_number'      => fake()->numerify('############'),
            'cccd_front_image' => $frontImage,
            'cccd_back_image'  => $backImage,
            'kyc_status'       => KycStatus::Approved,
            'created_at'       => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at'       => fake()->dateTimeBetween('-1 month', 'now'),
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
