<?php

namespace Database\Seeders;

use App\Enums\GeneralStatus;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    private array $platformIconSources = [
        'steam'           => 'https://cdn.simpleicons.org/steam/black',
        'epic-games'      => 'https://cdn.simpleicons.org/epicgames/black',
        'gog'             => 'https://cdn.simpleicons.org/gog.com/black',
        'ea-app'          => 'https://cdn.simpleicons.org/ea/black',
        'ubisoft-connect' => 'https://cdn.simpleicons.org/ubisoft/black',
        'battle-net'      => 'https://cdn.simpleicons.org/battledotnet/black',
        'xbox'            => 'https://cdn-icons-png.flaticon.com/512/1/1321.png',
        'playstation'     => 'https://cdn.simpleicons.org/playstation/black',
        'nintendo'        => 'https://cdn-icons-png.flaticon.com/128/871/871377.png',
        'humble'          => 'https://cdn.simpleicons.org/humblebundle/black',
        'gmg'             => 'https://static.rakuten.com/img/store/13501/13501-GreenManGaming-square-fullcolor.png',
        'rockstar'        => 'https://cdn.simpleicons.org/rockstargames/black',
        'microsoft'       => 'https://cdn-icons-png.flaticon.com/128/732/732221.png',
        'itch'            => 'https://cdn.simpleicons.org/itch.io/black',
        'riot'            => 'https://cdn.simpleicons.org/riotgames/black',
        'adobe'           => 'https://cdn-icons-png.flaticon.com/128/888/888835.png',
        'spotify'         => 'https://cdn.simpleicons.org/spotify/black',
        'netflix'         => 'https://cdn.simpleicons.org/netflix/black',
    ];

    private array $osIconSources = [
        'windows10' => 'https://cdn-icons-png.flaticon.com/128/732/732225.png',
        'windows11' => 'https://cdn-icons-png.flaticon.com/128/2952/2952245.png',
        'windows7'  => 'https://cdn-icons-png.flaticon.com/128/232/232411.png',
        'windows8'  => 'https://cdn-icons-png.flaticon.com/128/882/882702.png',
        'macos'     => 'https://cdn.simpleicons.org/apple/black',
        'ubuntu'    => 'https://cdn.simpleicons.org/ubuntu/black',
        'linux'     => 'https://cdn.simpleicons.org/linux/black',
        'steamos'   => 'https://cdn.simpleicons.org/steam/black',
        'chromeos'  => 'https://cdn.simpleicons.org/googlechrome/black',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->downloadIcons();
        $this->seedRegions();
        $this->seedPlatforms();
        $this->seedOperatingSystems();
    }

    protected function downloadIcons(): void
    {
        $disk = config('filesystems.public_disk', 'public');

        Storage::disk($disk)->makeDirectory('icons/platforms');
        Storage::disk($disk)->makeDirectory('icons/os');

        $this->downloadIconGroup($this->platformIconSources, 'icons/platforms', $disk);
        $this->downloadIconGroup($this->osIconSources, 'icons/os', $disk);
    }

    protected function downloadIconGroup(array $icons, string $basePath, string $disk): void
    {
        foreach ($icons as $name => $url) {
            $filePath = "{$basePath}/{$name}.svg";

            $response = Http::timeout(10)
                ->retry(2, 200)
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Icon download failed', [
                    'url'  => $url,
                    'path' => $filePath,
                ]);

                continue;
            }

            Storage::disk($disk)->put($filePath, $response->body());
        }
    }

    protected function seedRegions(): void
    {
        $regions = [
            // Global/Universal regions
            ['name' => 'Global', 'code' => 'GLOBAL', 'parent' => null],
            ['name' => 'Asia', 'code' => 'ASIA', 'parent' => null],
            ['name' => 'Europe', 'code' => 'EU', 'parent' => null],
            ['name' => 'North America', 'code' => 'NA', 'parent' => null],
            ['name' => 'South America', 'code' => 'SA', 'parent' => null],

            // Country-level regions
            ['name' => 'Vietnam', 'code' => 'VN', 'parent' => 'Asia'],
            ['name' => 'Southeast Asia', 'code' => 'SEA', 'parent' => 'Asia'],
            ['name' => 'Singapore', 'code' => 'SG', 'parent' => 'Asia'],
            ['name' => 'Thailand', 'code' => 'TH', 'parent' => 'Asia'],
            ['name' => 'Japan', 'code' => 'JP', 'parent' => 'Asia'],
            ['name' => 'South Korea', 'code' => 'KR', 'parent' => 'Asia'],
            ['name' => 'China', 'code' => 'CN', 'parent' => 'Asia'],
            ['name' => 'India', 'code' => 'IN', 'parent' => 'Asia'],
            ['name' => 'Philippines', 'code' => 'PH', 'parent' => 'Asia'],
            ['name' => 'Indonesia', 'code' => 'ID', 'parent' => 'Asia'],
            ['name' => 'Malaysia', 'code' => 'MY', 'parent' => 'Asia'],

            ['name' => 'United States', 'code' => 'US', 'parent' => 'North America'],
            ['name' => 'Canada', 'code' => 'CA', 'parent' => 'North America'],
            ['name' => 'Mexico', 'code' => 'MX', 'parent' => 'North America'],

            ['name' => 'United Kingdom', 'code' => 'GB', 'parent' => 'Europe'],
            ['name' => 'Germany', 'code' => 'DE', 'parent' => 'Europe'],
            ['name' => 'France', 'code' => 'FR', 'parent' => 'Europe'],
            ['name' => 'Spain', 'code' => 'ES', 'parent' => 'Europe'],
            ['name' => 'Italy', 'code' => 'IT', 'parent' => 'Europe'],
            ['name' => 'Netherlands', 'code' => 'NL', 'parent' => 'Europe'],
            ['name' => 'Poland', 'code' => 'PL', 'parent' => 'Europe'],
            ['name' => 'Russia', 'code' => 'RU', 'parent' => 'Europe'],
            ['name' => 'Turkey', 'code' => 'TR', 'parent' => 'Europe'],
            ['name' => 'Ukraine', 'code' => 'UA', 'parent' => 'Europe'],
            ['name' => 'Sweden', 'code' => 'SE', 'parent' => 'Europe'],
            ['name' => 'Norway', 'code' => 'NO', 'parent' => 'Europe'],

            ['name' => 'Brazil', 'code' => 'BR', 'parent' => 'South America'],
            ['name' => 'Argentina', 'code' => 'AR', 'parent' => 'South America'],
            ['name' => 'Chile', 'code' => 'CL', 'parent' => 'South America'],

            ['name' => 'Australia', 'code' => 'AU', 'parent' => null],
            ['name' => 'New Zealand', 'code' => 'NZ', 'parent' => null],
            ['name' => 'Middle East', 'code' => 'ME', 'parent' => null],
            ['name' => 'Africa', 'code' => 'AF', 'parent' => null],
        ];

        $createdRegions = [];

        foreach ($regions as $regionData) {
            if ($regionData['parent'] === null) {
                $region = Region::firstOrCreate(
                    ['flag_code' => $regionData['code']],
                    [
                        'name'   => $regionData['name'],
                        'slug'   => Str::slug($regionData['name']),
                        'status' => GeneralStatus::Active,
                    ]
                );
                $createdRegions[$regionData['code']] = $region;
            }
        }

        foreach ($regions as $regionData) {
            if ($regionData['parent'] !== null && isset($createdRegions[$regionData['parent']])) {
                Region::firstOrCreate(
                    ['flag_code' => $regionData['code']],
                    [
                        'parent_id' => $createdRegions[$regionData['parent']]->id,
                        'name'      => $regionData['name'],
                        'slug'      => Str::slug($regionData['name']),
                        'status'    => GeneralStatus::Active,
                    ]
                );
            }
        }
    }

    protected function seedPlatforms(): void
    {
        $platforms = [
            ['name' => 'Steam', 'url' => 'https://store.steampowered.com', 'icon' => 'icons/platforms/steam.svg'],
            ['name' => 'Epic Games Store', 'url' => 'https://www.epicgames.com/store', 'icon' => 'icons/platforms/epic-games.svg'],
            ['name' => 'GOG.com', 'url' => 'https://www.gog.com', 'icon' => 'icons/platforms/gog.svg'],
            ['name' => 'Origin / EA App', 'url' => 'https://www.ea.com/games/ea-app', 'icon' => 'icons/platforms/ea-app.svg'],
            ['name' => 'Ubisoft Connect', 'url' => 'https://ubisoftconnect.com', 'icon' => 'icons/platforms/ubisoft-connect.svg'],
            ['name' => 'Battle.net', 'url' => 'https://www.blizzard.com', 'icon' => 'icons/platforms/battle-net.svg'],
            ['name' => 'Xbox App', 'url' => 'https://www.xbox.com', 'icon' => 'icons/platforms/xbox.svg'],
            ['name' => 'PlayStation Store', 'url' => 'https://store.playstation.com', 'icon' => 'icons/platforms/playstation.svg'],
            ['name' => 'Nintendo eShop', 'url' => 'https://www.nintendo.com/eshop', 'icon' => 'icons/platforms/nintendo.svg'],
            ['name' => 'Humble Bundle', 'url' => 'https://www.humblebundle.com', 'icon' => 'icons/platforms/humble.svg'],
            ['name' => 'Green Man Gaming', 'url' => 'https://www.greenmangaming.com', 'icon' => 'icons/platforms/gmg.svg'],
            ['name' => 'Rockstar Games Launcher', 'url' => 'https://www.rockstargames.com', 'icon' => 'icons/platforms/rockstar.svg'],
            ['name' => 'Microsoft Store', 'url' => 'https://www.microsoft.com/store', 'icon' => 'icons/platforms/microsoft.svg'],
            ['name' => 'itch.io', 'url' => 'https://itch.io', 'icon' => 'icons/platforms/itch.svg'],
            ['name' => 'Riot Client', 'url' => 'https://www.riotgames.com', 'icon' => 'icons/platforms/riot.svg'],
            ['name' => 'EA App', 'url' => 'https://www.ea.com/games/ea-app', 'icon' => 'icons/platforms/ea-app.svg'],
            ['name' => 'Adobe Creative Cloud', 'url' => 'https://www.adobe.com/creativecloud.html', 'icon' => 'icons/platforms/adobe.svg'],
            ['name' => 'Spotify', 'url' => 'https://www.spotify.com', 'icon' => 'icons/platforms/spotify.svg'],
            ['name' => 'Netflix', 'url' => 'https://www.netflix.com', 'icon' => 'icons/platforms/netflix.svg'],
        ];

        foreach ($platforms as $platformData) {
            Platform::firstOrCreate(
                ['slug' => Str::slug($platformData['name'])],
                [
                    'name'      => $platformData['name'],
                    'icon_path' => $platformData['icon'],
                    'base_url'  => $platformData['url'],
                    'status'    => GeneralStatus::Active,
                ]
            );
        }
    }

    protected function seedOperatingSystems(): void
    {
        $operatingSystems = [
            ['name' => 'Windows 10', 'icon' => 'icons/os/windows10.svg'],
            ['name' => 'Windows 11', 'icon' => 'icons/os/windows11.svg'],
            ['name' => 'Windows 7', 'icon' => 'icons/os/windows7.svg'],
            ['name' => 'Windows 8', 'icon' => 'icons/os/windows8.svg'],
            ['name' => 'macOS', 'icon' => 'icons/os/macos.svg'],
            ['name' => 'macOS Ventura', 'icon' => 'icons/os/macos.svg'],
            ['name' => 'macOS Sonoma', 'icon' => 'icons/os/macos.svg'],
            ['name' => 'macOS Sequoia', 'icon' => 'icons/os/macos.svg'],
            ['name' => 'Ubuntu 22.04 LTS', 'icon' => 'icons/os/ubuntu.svg'],
            ['name' => 'Ubuntu 24.04 LTS', 'icon' => 'icons/os/ubuntu.svg'],
            ['name' => 'Linux (General)', 'icon' => 'icons/os/linux.svg'],
            ['name' => 'SteamOS', 'icon' => 'icons/os/steamos.svg'],
            ['name' => 'ChromeOS', 'icon' => 'icons/os/chromeos.svg'],
        ];

        foreach ($operatingSystems as $osData) {
            OperatingSystem::firstOrCreate(
                ['slug' => Str::slug($osData['name'])],
                [
                    'name'      => $osData['name'],
                    'icon_path' => $osData['icon'],
                    'status'    => GeneralStatus::Active,
                ]
            );
        }
    }
}
