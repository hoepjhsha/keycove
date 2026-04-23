<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement([Gender::Male, Gender::Female]);

        return [
            'user_id'      => User::factory(),
            'first_name'   => fake()->firstName($gender === Gender::Male ? 'male' : 'female'),
            'last_name'    => fake()->lastName(),
            'avatar'       => fake()->imageUrl(200, 200, 'people', true, 'avatar'),
            'dob'          => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'gender'       => $gender,
            'phone_number' => fake()->phoneNumber(),
            'bio'          => fake()->optional(0.7)->sentence(15),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user?->id ?? User::factory()->create()->id,
            ];
        });
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender'     => Gender::Male,
            'first_name' => fake()->firstName('male'),
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender'     => Gender::Female,
            'first_name' => fake()->firstName('female'),
        ]);
    }

    public function withoutBio(): static
    {
        return $this->state(fn (array $attributes) => [
            'bio' => null,
        ]);
    }
}
