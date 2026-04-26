<?php

namespace Database\Seeders;

use App\Enums\GeneralStatus;
use App\Enums\KycStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Category;
use App\Models\OperatingSystem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedProducts();
    }

    protected function seedProducts(): void
    {
        $products = $this->getProductsData();

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($productData['name'])],
                [
                    'name'                 => $productData['name'],
                    'image_thumbnail_path' => 'products/thumbnails/'.Str::slug($productData['name']).'.jpg',
                    'publisher'            => $productData['publisher'],
                    'developer'            => $productData['developer'],
                    'release_date'         => $productData['release_date'],
                    'description'          => $productData['description'],
                    'system_requirement'   => $productData['system_requirement'],
                    'status'               => GeneralStatus::Active,
                ]
            );

            if (! empty($productData['categories'])) {
                $categoryIds = [];

                foreach ($productData['categories'] as $cat) {
                    $slug = Str::slug($cat);

                    $category = Category::where('slug', $cat)
                        ->orWhere('slug', $slug)
                        ->orWhereRaw('LOWER(name) = ?', [strtolower($cat)])
                        ->first();

                    if (! $category) {
                        $category = Category::create([
                            'name'   => Str::title(str_replace('-', ' ', $cat)),
                            'slug'   => $slug,
                            'status' => GeneralStatus::Active,
                        ]);
                    }

                    $categoryIds[] = $category->id;
                }

                if (! empty($categoryIds)) {
                    $product->categories()->syncWithoutDetaching($categoryIds);
                }
            }

            $this->createVariants($product, $productData['variants'] ?? []);
        }
    }

    protected function createVariants(Product $product, array $variantConfigs): void
    {
        $platforms = Platform::where('status', GeneralStatus::Active)->get();
        $regions = Region::whereNull('parent_id')->where('status', GeneralStatus::Active)->get();
        $oses = OperatingSystem::where('status', GeneralStatus::Active)->get();
        $sellers = Seller::where('kyc_status', KycStatus::Approved)->get();

        if ($sellers->isEmpty()) {
            return;
        }

        $defaultVariants = [
            ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe']],
            ['platform' => 'Steam', 'region' => 'Southeast Asia', 'os' => 'Windows 10', 'editions' => ['Standard']],
            ['platform' => 'Steam', 'region' => 'Europe', 'os' => 'Windows 10', 'editions' => ['Standard']],
            ['platform' => 'Epic Games Store', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
            ['platform' => 'Steam', 'region' => 'United States', 'os' => 'Windows 11', 'editions' => ['Standard']],
        ];

        $variantsToCreate = ! empty($variantConfigs) ? $variantConfigs : $defaultVariants;

        foreach ($variantsToCreate as $variantConfig) {
            $platform = $platforms->firstWhere('slug', Str::slug($variantConfig['platform']));
            $region = $regions->firstWhere('slug', Str::slug($variantConfig['region']));
            $os = $oses->firstWhere('slug', Str::slug($variantConfig['os']));

            if (! $platform || ! $region || ! $os) {
                continue;
            }

            $editions = $variantConfig['editions'] ?? ['Standard'];

            foreach ($editions as $edition) {
                $variant = ProductVariant::create([
                    'product_id'  => $product->id,
                    'region_id'   => $region->id,
                    'platform_id' => $platform->id,
                    'os_id'       => $os->id,
                    'edition'     => $edition,
                    'status'      => ProductVariantStatus::Active,
                ]);

                // Create listings for this variant
                $this->createListings($variant, $sellers, $productData['price_range'] ?? [9.99, 59.99]);
            }
        }
    }

    protected function createListings(ProductVariant $variant, $sellers, array $priceRange): void
    {
        $numListings = random_int(1, 3);
        $sellers = $sellers->shuffle()->take($numListings);

        foreach ($sellers as $seller) {
            $price = fake()->randomFloat(2, $priceRange[0], $priceRange[1]);
            $status = fake()->randomElement([
                ProductListingStatus::Active,
                ProductListingStatus::Active,
                ProductListingStatus::Active,
                ProductListingStatus::Active,
                ProductListingStatus::Pending,
                ProductListingStatus::Hidden,
            ]);

            $listing = ProductListing::create([
                'variant_id'  => $variant->id,
                'seller_id'   => $seller->id,
                'price'       => $price,
                'stock_count' => 0,
                'status'      => $status,
            ]);

            $this->createKeys($listing, $status);
        }
    }

    protected function createKeys(ProductListing $listing, ProductListingStatus $status): void
    {
        $numKeys = $status === ProductListingStatus::Active ? random_int(15, 50) : random_int(5, 15);

        for ($i = 0; $i < $numKeys; $i++) {
            $keyStatus = fake()->randomElement([
                ProductKeyStatus::Available,
                ProductKeyStatus::Available,
                ProductKeyStatus::Available,
                ProductKeyStatus::Available,
                ProductKeyStatus::Available,
                ProductKeyStatus::Sold,
                ProductKeyStatus::Reserved,
            ]);

            $keyCode = $this->generateKeyCode();

            ProductKey::create([
                'listing_id'    => $listing->id,
                'key_code'      => $keyCode,
                'key_hash'      => hash('sha256', $keyCode),
                'status'        => $keyStatus,
                'order_item_id' => null,
            ]);
        }

        $listing->update([
            'stock_count' => $listing->keys()->where('status', ProductKeyStatus::Available)->count(),
        ]);
    }

    protected function generateKeyCode(): string
    {
        $segments = [];
        for ($i = 0; $i < 5; $i++) {
            $segments[] = strtoupper(Str::random(5));
        }

        return implode('-', $segments);
    }

    protected function getProductsData(): array
    {
        return [
            // AAA Games - Action/Adventure
            [
                'name'         => 'Cyberpunk 2077',
                'publisher'    => 'CD Projekt Red',
                'developer'    => 'CD Projekt Red',
                'release_date' => '2020-12-10',
                'description'  => 'Cyberpunk 2077 is an open-world, action-adventure RPG set in the dark future of Night City — a dangerous megalopolis obsessed with power, glamour and endless body modification.',
                'categories'   => ['action-games', 'rpg-games', 'open-world'],
                'price_range'  => [19.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Ultimate Edition']],
                    ['platform' => 'GOG.com', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                    ['platform' => 'Epic Games Store', 'region' => 'Global', 'os' => 'Windows 11', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Red Dead Redemption 2',
                'publisher'    => 'Rockstar Games',
                'developer'    => 'Rockstar Games',
                'release_date' => '2019-11-05',
                'description'  => 'America, 1899. The end of the Wild West era has begun. After a robbery goes badly wrong, Arthur Morgan and the Van der Linde gang are forced to flee.',
                'categories'   => ['action-games', 'open-world', 'adventure-games'],
                'price_range'  => [24.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Ultimate Edition']],
                    ['platform' => 'Rockstar Games Launcher', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Grand Theft Auto V',
                'publisher'    => 'Rockstar Games',
                'developer'    => 'Rockstar North',
                'release_date' => '2015-04-14',
                'description'  => 'Grand Theft Auto V for PC features a range of visual and technical enhancements that make the game more immersive than ever, including increased draw distances, 4K resolution, and 60 frames per second gameplay.',
                'categories'   => ['action-games', 'open-world', 'multiplayer-games'],
                'price_range'  => [9.99, 29.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Premium Edition']],
                    ['platform' => 'Rockstar Games Launcher', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Premium Edition']],
                    ['platform' => 'Epic Games Store', 'region' => 'Global', 'os' => 'Windows 11', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],
            [
                'name'         => 'Elden Ring',
                'publisher'    => 'Bandai Namco',
                'developer'    => 'FromSoftware',
                'release_date' => '2022-02-25',
                'description'  => 'THE NEW FANTASY ACTION RPG. Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.',
                'categories'   => ['rpg-games', 'action-games', 'souls-like'],
                'price_range'  => [29.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe Edition']],
                    ['platform' => 'Steam', 'region' => 'Southeast Asia', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Baldur\'s Gate 3',
                'publisher'    => 'Larian Studios',
                'developer'    => 'Larian Studios',
                'release_date' => '2023-08-03',
                'description'  => 'Gather your party, and return to the Forgotten Realms in a tale of fellowship and betrayal, sacrifice and survival, and the lure of absolute power.',
                'categories'   => ['rpg-games', 'strategy-games', 'multiplayer-games'],
                'price_range'  => [39.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Digital Deluxe Edition']],
                    ['platform' => 'GOG.com', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Hogwarts Legacy',
                'publisher'    => 'Warner Bros. Games',
                'developer'    => 'Avalanche Software',
                'release_date' => '2023-02-10',
                'description'  => 'Experience Hogwarts in the 1800s. Your legacy is what you make it. Cast spells, brew potions, and tame fantastical beasts in this open-world action RPG.',
                'categories'   => ['rpg-games', 'action-games', 'adventure-games'],
                'price_range'  => [29.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Digital Deluxe Edition']],
                    ['platform' => 'Epic Games Store', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'God of War',
                'publisher'    => 'Sony Interactive Entertainment',
                'developer'    => 'Santa Monica Studio',
                'release_date' => '2022-01-14',
                'description'  => 'His vengeance against the Gods of Olympus years behind him, Kratos now lives as a man in the realm of Norse Gods and monsters.',
                'categories'   => ['action-games', 'adventure-games'],
                'price_range'  => [19.99, 49.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Digital Deluxe Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Starfield',
                'publisher'    => 'Bethesda Softworks',
                'developer'    => 'Bethesda Game Studios',
                'release_date' => '2023-09-06',
                'description'  => 'Starfield is the first new universe in 25 years from Bethesda Game Studios, the award-winning creators of The Elder Scrolls V: Skyrim and Fallout 4.',
                'categories'   => ['rpg-games', 'open-world', 'sci-fi-games'],
                'price_range'  => [39.99, 69.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 11', 'editions' => ['Standard', 'Premium Edition']],
                    ['platform' => 'Xbox App', 'region' => 'Global', 'os' => 'Windows 11', 'editions' => ['Standard', 'Premium Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],

            // Competitive/Multiplayer Games
            [
                'name'         => 'Counter-Strike 2',
                'publisher'    => 'Valve Corporation',
                'developer'    => 'Valve',
                'release_date' => '2023-09-27',
                'description'  => 'Counter-Strike 2 is the largest technical leap forward in Counter-Strike\'s history, ensuring new features and updates for years to come.',
                'categories'   => ['multiplayer-games', 'shooter-games', 'competitive-games'],
                'price_range'  => [4.99, 14.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Prime Status Upgrade']],
                    ['platform' => 'Steam', 'region' => 'Southeast Asia', 'os' => 'Windows 10', 'editions' => ['Prime Status Upgrade']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],
            [
                'name'         => 'VALORANT',
                'publisher'    => 'Riot Games',
                'developer'    => 'Riot Games',
                'release_date' => '2020-06-02',
                'description'  => 'VALORANT is a free-to-play 5v5 character-based tactical shooter. Blend your style and experience on the field.',
                'categories'   => ['multiplayer-games', 'shooter-games', 'competitive-games'],
                'price_range'  => [9.99, 99.99],
                'variants'     => [
                    ['platform' => 'Riot Client', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['VP Points 1000', 'VP Points 5000', 'VP Points 10000']],
                    ['platform' => 'Riot Client', 'region' => 'Southeast Asia', 'os' => 'Windows 10', 'editions' => ['VP Points 1000', 'VP Points 5000']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],
            [
                'name'         => 'Rainbow Six Siege',
                'publisher'    => 'Ubisoft',
                'developer'    => 'Ubisoft Montreal',
                'release_date' => '2015-12-01',
                'description'  => 'Tom Clancy\'s Rainbow Six Siege is an elite, tactical team-based shooter where superior planning and execution triumph.',
                'categories'   => ['multiplayer-games', 'shooter-games', 'competitive-games'],
                'price_range'  => [7.99, 39.99],
                'variants'     => [
                    ['platform' => 'Ubisoft Connect', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe', 'Ultimate']],
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],

            // Sports/Racing
            [
                'name'         => 'EA Sports FC 24',
                'publisher'    => 'Electronic Arts',
                'developer'    => 'EA Vancouver',
                'release_date' => '2023-09-29',
                'description'  => 'EA SPORTS FC™ 24 welcomes you to The World\'s Game: the most true-to-football experience ever with the HyperMotionV',
                'categories'   => ['sports-games', 'multiplayer-games'],
                'price_range'  => [19.99, 69.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Ultimate Edition']],
                    ['platform' => 'EA App', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Ultimate Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],
            [
                'name'         => 'Forza Horizon 5',
                'publisher'    => 'Microsoft Studios',
                'developer'    => 'Playground Games',
                'release_date' => '2021-11-09',
                'description'  => 'Explore the vibrant open world landscapes of Mexico with limitless, fun driving action in hundreds of the world\'s greatest cars.',
                'categories'   => ['racing-games', 'open-world'],
                'price_range'  => [19.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe', 'Premium']],
                    ['platform' => 'Xbox App', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],

            // Indie Games
            [
                'name'         => 'Hades',
                'publisher'    => 'Supergiant Games',
                'developer'    => 'Supergiant Games',
                'release_date' => '2020-09-17',
                'description'  => 'Defy the god of the dead as you hack and slash out of the Underworld in this rogue-like dungeon crawler from the creators of Bastion and Transistor.',
                'categories'   => ['indie-games', 'action-games', 'roguelike-games'],
                'price_range'  => [9.99, 24.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                    ['platform' => 'GOG.com', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                    ['platform' => 'Epic Games Store', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],
            [
                'name'         => 'Stardew Valley',
                'publisher'    => 'ConcernedApe',
                'developer'    => 'ConcernedApe',
                'release_date' => '2016-02-26',
                'description'  => 'You\'ve inherited your grandfather\'s old farm plot in Stardew Valley. Armed with hand-me-down tools and a few coins, you set out to begin your new life.',
                'categories'   => ['indie-games', 'simulation-games', 'rpg-games'],
                'price_range'  => [7.99, 14.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                    ['platform' => 'GOG.com', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],
            [
                'name'         => 'Hollow Knight',
                'publisher'    => 'Team Cherry',
                'developer'    => 'Team Cherry',
                'release_date' => '2017-02-24',
                'description'  => 'Forge your own path in Hollow Knight! An epic action adventure through a vast ruined kingdom of insects and heroes.',
                'categories'   => ['indie-games', 'action-games', 'platformer-games'],
                'price_range'  => [7.49, 14.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],

            // Software & Services
            [
                'name'         => 'Microsoft 365 Personal',
                'publisher'    => 'Microsoft',
                'developer'    => 'Microsoft',
                'release_date' => '2020-04-28',
                'description'  => 'Get premium Office apps including Word, Excel, PowerPoint, Outlook, and 1TB of OneDrive cloud storage for one person.',
                'categories'   => ['productivity-software', 'subscription-services'],
                'price_range'  => [29.99, 69.99],
                'variants'     => [
                    ['platform' => 'Microsoft Store', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['1 Year Subscription']],
                    ['platform' => 'Microsoft Store', 'region' => 'Global', 'os' => 'macOS', 'editions' => ['1 Year Subscription']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],
            [
                'name'         => 'Adobe Creative Cloud All Apps',
                'publisher'    => 'Adobe',
                'developer'    => 'Adobe',
                'release_date' => '2021-06-15',
                'description'  => 'Get 20+ creative apps including Photoshop, Illustrator, Premiere Pro, After Effects, and more.',
                'categories'   => ['productivity-software', 'design-software'],
                'price_range'  => [39.99, 59.99],
                'variants'     => [
                    ['platform' => 'Adobe Creative Cloud', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['1 Year Subscription']],
                    ['platform' => 'Adobe Creative Cloud', 'region' => 'Global', 'os' => 'macOS', 'editions' => ['1 Year Subscription']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],
            [
                'name'         => 'Spotify Premium',
                'publisher'    => 'Spotify',
                'developer'    => 'Spotify',
                'release_date' => '2022-01-01',
                'description'  => 'Ad-free music listening with offline mode and on-demand playback. Download and listen to songs offline.',
                'categories'   => ['subscription-services', 'entertainment-services'],
                'price_range'  => [9.99, 49.99],
                'variants'     => [
                    ['platform' => 'Spotify', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['1 Month', '3 Months', '6 Months', '1 Year']],
                ],
                'system_requirement' => [],
            ],
            [
                'name'         => 'Netflix Premium',
                'publisher'    => 'Netflix',
                'developer'    => 'Netflix',
                'release_date' => '2022-01-01',
                'description'  => 'Watch unlimited movies and TV shows on your phone, tablet, laptop, and TV. 4 screens simultaneously in Ultra HD.',
                'categories'   => ['subscription-services', 'entertainment-services'],
                'price_range'  => [14.99, 89.99],
                'variants'     => [
                    ['platform' => 'Netflix', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['1 Month', '3 Months', '6 Months', '1 Year']],
                    ['platform' => 'Netflix', 'region' => 'Southeast Asia', 'os' => 'Windows 10', 'editions' => ['1 Month', '3 Months', '6 Months']],
                ],
                'system_requirement' => [],
            ],

            // More Popular Games
            [
                'name'         => 'The Witcher 3: Wild Hunt',
                'publisher'    => 'CD Projekt Red',
                'developer'    => 'CD Projekt Red',
                'release_date' => '2015-05-19',
                'description'  => 'You are Geralt of Rivia, mercenary monster slayer. Before you stands a war-torn, monster-infested continent you can explore at will.',
                'categories'   => ['rpg-games', 'open-world', 'action-games'],
                'price_range'  => [9.99, 39.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Game of the Year Edition']],
                    ['platform' => 'GOG.com', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Game of the Year Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],
            [
                'name'         => 'Diablo IV',
                'publisher'    => 'Blizzard Entertainment',
                'developer'    => 'Blizzard Entertainment',
                'release_date' => '2023-06-06',
                'description'  => 'Diablo IV is the ultimate action RPG experience, with endless evil to slaughter, formidable dungeons, and an ever-expanding world.',
                'categories'   => ['rpg-games', 'action-games', 'multiplayer-games'],
                'price_range'  => [39.99, 89.99],
                'variants'     => [
                    ['platform' => 'Battle.net', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe', 'Ultimate']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Resident Evil 4 Remake',
                'publisher'    => 'Capcom',
                'developer'    => 'Capcom',
                'release_date' => '2023-03-24',
                'description'  => 'Survival is just the beginning. Six years have passed since the biological disaster in Raccoon City.',
                'categories'   => ['action-games', 'horror-games'],
                'price_range'  => [24.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Sekiro: Shadows Die Twice',
                'publisher'    => 'Activision',
                'developer'    => 'FromSoftware',
                'release_date' => '2019-03-22',
                'description'  => 'Carve your own clever path to revenge in an all-new adventure from developer FromSoftware.',
                'categories'   => ['action-games', 'souls-like', 'adventure-games'],
                'price_range'  => [19.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Game of the Year Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],
            [
                'name'         => 'Dead Space Remake',
                'publisher'    => 'Electronic Arts',
                'developer'    => 'Motive Studio',
                'release_date' => '2023-01-27',
                'description'  => 'The sci-fi survival horror classic Dead Space returns, rebuilt from the ground up to offer an even deeper experience.',
                'categories'   => ['horror-games', 'action-games', 'sci-fi-games'],
                'price_range'  => [24.99, 69.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Digital Deluxe Edition']],
                    ['platform' => 'EA App', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
            [
                'name'         => 'Minecraft',
                'publisher'    => 'Microsoft Studios',
                'developer'    => 'Mojang Studios',
                'release_date' => '2011-11-18',
                'description'  => 'Explore randomly generated worlds and build amazing things, from simple homes to grand castles.',
                'categories'   => ['indie-games', 'sandbox-games', 'multiplayer-games'],
                'price_range'  => [19.99, 39.99],
                'variants'     => [
                    ['platform' => 'Microsoft Store', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Java & Bedrock Edition']],
                ],
                'system_requirement' => $this->getSystemRequirements('low'),
            ],
            [
                'name'         => 'Street Fighter 6',
                'publisher'    => 'Capcom',
                'developer'    => 'Capcom',
                'release_date' => '2023-06-02',
                'description'  => 'Here comes Capcom\'s newest fighter! Experience the next evolution of the fighting game genre.',
                'categories'   => ['fighting-games', 'multiplayer-games', 'competitive-games'],
                'price_range'  => [29.99, 59.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe', 'Ultimate']],
                ],
                'system_requirement' => $this->getSystemRequirements('medium'),
            ],
            [
                'name'         => 'Tekken 8',
                'publisher'    => 'Bandai Namco',
                'developer'    => 'Bandai Namco Studios',
                'release_date' => '2024-01-26',
                'description'  => 'Get ready for the next chapter in the legendary fighting game franchise. Tekken 8 features stunning graphics powered by Unreal Engine 5.',
                'categories'   => ['fighting-games', 'multiplayer-games', 'competitive-games'],
                'price_range'  => [39.99, 69.99],
                'variants'     => [
                    ['platform' => 'Steam', 'region' => 'Global', 'os' => 'Windows 10', 'editions' => ['Standard', 'Deluxe', 'Ultimate']],
                ],
                'system_requirement' => $this->getSystemRequirements('high'),
            ],
        ];
    }

    protected function getSystemRequirements(string $level): array
    {
        return match ($level) {
            'low' => [
                'os'        => 'Windows 10 64-bit',
                'processor' => 'Intel Core i3 or AMD equivalent',
                'memory'    => '4 GB RAM',
                'graphics'  => 'NVIDIA GeForce GTX 660 or AMD Radeon HD 7850',
                'directx'   => 'Version 11',
                'storage'   => '10 GB available space',
            ],
            'medium' => [
                'os'         => 'Windows 10 64-bit',
                'processor'  => 'Intel Core i5-8400 or AMD Ryzen 5 2600',
                'memory'     => '8 GB RAM',
                'graphics'   => 'NVIDIA GeForce GTX 1060 6GB or AMD Radeon RX 580 8GB',
                'directx'    => 'Version 12',
                'storage'    => '50 GB available space',
                'additional' => 'SSD recommended',
            ],
            'high' => [
                'os'         => 'Windows 10 64-bit',
                'processor'  => 'Intel Core i7-9700K or AMD Ryzen 7 3700X',
                'memory'     => '16 GB RAM',
                'graphics'   => 'NVIDIA GeForce RTX 3060 Ti or AMD Radeon RX 6700 XT',
                'directx'    => 'Version 12',
                'storage'    => '100 GB available space',
                'additional' => 'SSD required',
            ],
            default => [],
        };
    }
}
