<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Enums\GeneralStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Action',
            'Adventure',
            'RPG',
            'Strategy',
            'Simulation',
            'Sports',
            'Racing',
            'Shooter',
            'Platformer',
            'Puzzle',
            'Fighting',
            'Horror',
            'Survival',
            'MOBA',
            'MMORPG',
            'Battle Royale',
            'Sandbox',
            'Indie',
            'Casual',
            'Educational',
        ];

        $name = fake()->randomElement($categories);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).random_int(1, 100),
            'status' => GeneralStatus::Active,
        ];
    }

    public function withParent(?Category $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            $parentCategory = $parent ?? Category::factory()->create();

            return [
                'parent_id' => $parentCategory->id,
            ];
        });
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GeneralStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GeneralStatus::Inactive,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GeneralStatus::Hidden,
        ]);
    }
}
