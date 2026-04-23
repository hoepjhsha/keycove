<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\OperatingSystem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OperatingSystem>
 */
class OperatingSystemFactory extends Factory
{
    protected $model = OperatingSystem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $operatingSystems = [
            'Windows 11',
            'Windows 10',
            'macOS Sonoma',
            'macOS Ventura',
            'Ubuntu 22.04 LTS',
            'Ubuntu 24.04 LTS',
            'Debian 12',
            'Fedora 40',
            'Arch Linux',
            'Linux Mint',
            'SteamOS',
        ];

        $osName = fake()->randomElement($operatingSystems);

        return [
            'name'      => $osName,
            'slug'      => Str::slug($osName),
            'icon_path' => 'icons/os/'.Str::slug($osName).'.svg',
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
