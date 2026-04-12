<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Category;
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

    /**
     * Seed game genre categories with hierarchical structure.
     * Creates parent categories and their subcategories.
     */
    public static function seedGameGenres(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Action', 'slug' => 'action', 'parent_name' => null],
            ['id' => 2, 'name' => 'First-Person Shooter', 'slug' => 'fps', 'parent_name' => 'Action'],
            ['id' => 3, 'name' => 'Fighting', 'slug' => 'fighting', 'parent_name' => 'Action'],
            ['id' => 4, 'name' => 'Platformer', 'slug' => 'platformer', 'parent_name' => 'Action'],
            ['id' => 5, 'name' => 'Adventure', 'slug' => 'adventure', 'parent_name' => null],
            ['id' => 6, 'name' => 'Point-and-Click', 'slug' => 'point-and-click', 'parent_name' => 'Adventure'],
            ['id' => 7, 'name' => 'Role-Playing (RPG)', 'slug' => 'rpg', 'parent_name' => null],
            ['id' => 8, 'name' => 'Action RPG', 'slug' => 'action-rpg', 'parent_name' => 'Role-Playing (RPG)'],
            ['id' => 9, 'name' => 'MMORPG', 'slug' => 'mmorpg', 'parent_name' => 'Role-Playing (RPG)'],
            ['id' => 10, 'name' => 'JRPG', 'slug' => 'jrpg', 'parent_name' => 'Role-Playing (RPG)'],
            ['id' => 11, 'name' => 'Strategy', 'slug' => 'strategy', 'parent_name' => null],
            ['id' => 12, 'name' => 'Real-Time Strategy (RTS)', 'slug' => 'rts', 'parent_name' => 'Strategy'],
            ['id' => 13, 'name' => 'Turn-Based Strategy', 'slug' => 'tbs', 'parent_name' => 'Strategy'],
            ['id' => 14, 'name' => 'Simulation', 'slug' => 'simulation', 'parent_name' => null],
            ['id' => 15, 'name' => 'Life Simulation', 'slug' => 'life-simulation', 'parent_name' => 'Simulation'],
            ['id' => 16, 'name' => 'Racing', 'slug' => 'racing', 'parent_name' => 'Simulation'],
            ['id' => 17, 'name' => 'Sports', 'slug' => 'sports', 'parent_name' => null],
            ['id' => 18, 'name' => 'Puzzle', 'slug' => 'puzzle', 'parent_name' => null],
            ['id' => 19, 'name' => 'Horror', 'slug' => 'horror', 'parent_name' => null],
            ['id' => 20, 'name' => 'Survival Horror', 'slug' => 'survival-horror', 'parent_name' => 'Horror'],
        ];

        $parentMap = [];

        // First pass: create parent categories (parent_name = null)
        foreach ($categories as $cat) {
            if ($cat['parent_name'] === null) {
                $created = Category::firstOrCreate(
                    ['slug' => $cat['slug']],
                    [
                        'name' => $cat['name'],
                        'status' => GeneralStatus::Active,
                    ]
                );
                $parentMap[$cat['name']] = $created->id;
            }
        }

        // Second pass: create child categories with parent_id
        foreach ($categories as $cat) {
            if ($cat['parent_name'] !== null) {
                Category::firstOrCreate(
                    ['slug' => $cat['slug']],
                    [
                        'name' => $cat['name'],
                        'parent_id' => $parentMap[$cat['parent_name']],
                        'status' => GeneralStatus::Active,
                    ]
                );
            }
        }
    }
}
