<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Enums\GeneralStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gameNames = [
            'Cyberpunk 2077',
            'The Witcher 3: Wild Hunt',
            'Red Dead Redemption 2',
            'Grand Theft Auto V',
            'Elden Ring',
            'Dark Souls III',
            'Sekiro: Shadows Die Twice',
            'Baldur\'s Gate 3',
            'Starfield',
            'Hogwarts Legacy',
            'God of War',
            'Spider-Man Remastered',
            'Assassin\'s Creed Valhalla',
            'Far Cry 6',
            'Call of Duty: Modern Warfare II',
            'Battlefield 2042',
            'Halo Infinite',
            'Resident Evil 4 Remake',
            'Dead Space Remake',
            'The Last of Us Part I',
            'Horizon Zero Dawn',
            'Death Stranding',
            'Ghost of Tsushima',
            'Final Fantasy VII Remake',
            'Final Fantasy XVI',
            'Street Fighter 6',
            'Mortal Kombat 11',
            'Tekken 8',
            'Diablo IV',
            'Path of Exile',
            'Lost Ark',
            'World of Warcraft',
            'Guild Wars 2',
            'The Elder Scrolls Online',
            'Fallout 4',
            'Skyrim Special Edition',
            'Starfield',
            'Cities: Skylines II',
            'Civilization VI',
            'Total War: Warhammer III',
            'Age of Empires IV',
            'StarCraft II',
            'Counter-Strike 2',
            'VALORANT',
            'League of Legends',
            'Dota 2',
            'Fortnite',
            'Apex Legends',
            'PUBG: Battlegrounds',
            'Overwatch 2',
            'Rainbow Six Siege',
            'Rocket League',
            'FIFA 24',
            'NBA 2K24',
            'Madden NFL 24',
            'F1 23',
            'Gran Turismo 7',
            'Forza Horizon 5',
            'Need for Speed Unbound',
            'The Crew Motorfest',
            'Minecraft',
            'Terraria',
            'Stardew Valley',
            'Hollow Knight',
            'Celeste',
            'Hades',
            'Dead Cells',
            'Ori and the Will of the Wisps',
            'Cuphead',
            'It Takes Two',
            'A Way Out',
            'Brothers: A Tale of Two Sons',
            'Life is Strange',
            'Detroit: Become Human',
            'Heavy Rain',
            'Until Dawn',
            'The Quarry',
            'Resident Evil Village',
            'Silent Hill 2 Remake',
            'The Evil Within 2',
            'Outlast 2',
            'Amnesia: The Dark Descent',
            'SOMA',
            'Alien: Isolation',
            'Dead by Daylight',
            'Phasmophobia',
            'Lethal Company',
            'Subnautica',
            'The Forest',
            'Valheim',
            'Rust',
            'ARK: Survival Evolved',
            'Conan Exiles',
            'DayZ',
            '7 Days to Die',
            'Project Zomboid',
            'Green Hell',
        ];

        $publishers = [
            'CD Projekt Red',
            'Rockstar Games',
            'Electronic Arts',
            'Activision Blizzard',
            'Ubisoft',
            'Sony Interactive Entertainment',
            'Microsoft Studios',
            'Bandai Namco',
            'Capcom',
            'Square Enix',
            'Bethesda Softworks',
            'Valve Corporation',
            'Epic Games',
            'Riot Games',
            '2K Games',
            'Take-Two Interactive',
            'Sega',
            'Konami',
            'Nintendo',
            'Warner Bros. Games',
        ];

        $developers = [
            'CD Projekt Red',
            'Rockstar North',
            'FromSoftware',
            'Larian Studios',
            'Bethesda Game Studios',
            'Santa Monica Studio',
            'Insomniac Games',
            'Ubisoft Montreal',
            'Infinity Ward',
            'DICE',
            '343 Industries',
            'Capcom',
            'Guerrilla Games',
            'Kojima Productions',
            'Sucker Punch Productions',
            'Square Enix',
            'Valve',
            'Riot Games',
            'Blizzard Entertainment',
            'BioWare',
        ];

        $gameName = fake()->randomElement($gameNames);

        return [
            'category_id' => Category::factory(),
            'name' => $gameName,
            'slug' => Str::slug($gameName),
            'image_thumbnail_path' => 'products/thumbnails/'.Str::slug($gameName).'.jpg',
            'publisher' => fake()->randomElement($publishers),
            'developer' => fake()->randomElement($developers),
            'release_date' => fake()->dateTimeBetween('-10 years', '+6 months'),
            'description' => fake()->paragraphs(3, true),
            'system_requirement' => [
                'os' => fake()->randomElement(['Windows 10 64-bit', 'Windows 11', 'macOS 11 Big Sur']),
                'processor' => fake()->randomElement([
                    'Intel Core i5-8400 or AMD Ryzen 5 2600',
                    'Intel Core i7-9700K or AMD Ryzen 7 3700X',
                    'Intel Core i5-10400 or AMD Ryzen 5 3600',
                ]),
                'memory' => fake()->randomElement(['8 GB RAM', '12 GB RAM', '16 GB RAM', '32 GB RAM']),
                'graphics' => fake()->randomElement([
                    'NVIDIA GeForce GTX 1060 6GB or AMD Radeon RX 580 8GB',
                    'NVIDIA GeForce RTX 2060 or AMD Radeon RX 5700',
                    'NVIDIA GeForce RTX 3060 Ti or AMD Radeon RX 6700 XT',
                    'NVIDIA GeForce RTX 4070 or AMD Radeon RX 7800 XT',
                ]),
                'directx' => fake()->randomElement(['Version 11', 'Version 12']),
                'storage' => fake()->randomElement(['50 GB', '70 GB', '100 GB', '150 GB']).' available space',
                'additional' => fake()->optional()->randomElement([
                    'SSD recommended',
                    'Internet connection required',
                    'Requires 64-bit processor and operating system',
                ]),
            ],
            'status' => GeneralStatus::Active,
        ];
    }

    public function withCategory(?Category $category = null): static
    {
        return $this->state(function (array $attributes) use ($category) {
            return [
                'category_id' => $category?->id ?? Category::factory()->create()->id,
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
