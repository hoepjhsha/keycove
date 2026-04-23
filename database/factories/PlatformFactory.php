<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Platform>
 */
class PlatformFactory extends Factory
{
    protected $model = Platform::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platforms = [
            ['name' => 'Steam', 'url' => 'https://store.steampowered.com'],
            ['name' => 'Epic Games Store', 'url' => 'https://www.epicgames.com/store'],
            ['name' => 'GOG.com', 'url' => 'https://www.gog.com'],
            ['name' => 'Origin', 'url' => 'https://www.origin.com'],
            ['name' => 'Uplay', 'url' => 'https://uplay.ubisoft.com'],
            ['name' => 'Battle.net', 'url' => 'https://www.blizzard.com'],
            ['name' => 'Xbox Game Pass', 'url' => 'https://www.xbox.com/gamepass'],
            ['name' => 'PlayStation Store', 'url' => 'https://store.playstation.com'],
            ['name' => 'Nintendo eShop', 'url' => 'https://www.nintendo.com/eshop'],
            ['name' => 'Humble Bundle', 'url' => 'https://www.humblebundle.com'],
            ['name' => 'Green Man Gaming', 'url' => 'https://www.greenmangaming.com'],
            ['name' => 'Rockstar Games Launcher', 'url' => 'https://www.rockstargames.com'],
        ];

        $platform = fake()->randomElement($platforms);

        return [
            'name'      => $platform['name'],
            'slug'      => Str::slug($platform['name']),
            'icon_path' => 'icons/platforms/'.Str::slug($platform['name']).'.svg',
            'base_url'  => $platform['url'],
            'status'    => GeneralStatus::Active,
        ];
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
