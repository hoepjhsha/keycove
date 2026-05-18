<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US'],
            ['name' => 'United Kingdom', 'code' => 'GB'],
            ['name' => 'Germany', 'code' => 'DE'],
            ['name' => 'France', 'code' => 'FR'],
            ['name' => 'Japan', 'code' => 'JP'],
            ['name' => 'South Korea', 'code' => 'KR'],
            ['name' => 'Canada', 'code' => 'CA'],
            ['name' => 'Australia', 'code' => 'AU'],
            ['name' => 'Brazil', 'code' => 'BR'],
            ['name' => 'Russia', 'code' => 'RU'],
            ['name' => 'China', 'code' => 'CN'],
            ['name' => 'Vietnam', 'code' => 'VN'],
            ['name' => 'Singapore', 'code' => 'SG'],
            ['name' => 'India', 'code' => 'IN'],
            ['name' => 'Mexico', 'code' => 'MX'],
            ['name' => 'Spain', 'code' => 'ES'],
            ['name' => 'Italy', 'code' => 'IT'],
            ['name' => 'Netherlands', 'code' => 'NL'],
            ['name' => 'Poland', 'code' => 'PL'],
            ['name' => 'Sweden', 'code' => 'SE'],
        ];

        $country = fake()->randomElement($countries);
        $slug = Str::slug($country['name']).'-'.Str::lower(Str::random(8));

        return [
            'parent_id' => null,
            'name'      => $country['name'],
            'slug'      => $slug,
            'flag_code' => $country['code'].'-'.Str::lower(Str::random(4)),
            'status'    => GeneralStatus::Active,
        ];
    }

    public function withParent(?Region $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            $parentRegion = $parent ?? Region::factory()->country()->create();

            return [
                'parent_id' => $parentRegion->id,
                'name'      => fake()->state(),
            ];
        });
    }

    public function country(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    public function asState(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => Region::factory()->country()->create()->id,
                'name'      => fake()->state(),
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
