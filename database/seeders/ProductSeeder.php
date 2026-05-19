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
use DateTimeInterface;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private int $usdToVndRate = 25500;

    /** @var list<int> */
    private array $activeSellerIds = [];

    // Template quản lý Variant cho gọn gàng, bao phủ cấu trúc Steam thực tế
    private array $variantTemplates = [
        'steam_aaa' => [
            ['platform' => 'steam', 'region_code' => 'GLOBAL', 'os' => 'windows-10', 'editions' => ['Standard', 'Deluxe Edition', 'Ultimate Edition']],
            ['platform' => 'steam', 'region_code' => 'TR', 'os' => 'windows-10', 'editions' => ['Standard', 'Ultimate Edition']],
            ['platform' => 'steam', 'region_code' => 'AR', 'os' => 'windows-10', 'editions' => ['Standard']],
            ['platform' => 'steam', 'region_code' => 'VN', 'os' => 'windows-10', 'editions' => ['Standard']],
        ],
        'steam_indie' => [
            ['platform' => 'steam', 'region_code' => 'GLOBAL', 'os' => 'windows-10', 'editions' => ['Standard']],
            ['platform' => 'steam', 'region_code' => 'TR', 'os' => 'windows-10', 'editions' => ['Standard']],
            ['platform' => 'steam', 'region_code' => 'AR', 'os' => 'windows-10', 'editions' => ['Standard']],
        ],
        'epic_games' => [
            ['platform' => 'epic-games-store', 'region_code' => 'GLOBAL', 'os' => 'windows-10', 'editions' => ['Standard', 'Deluxe Edition']],
        ],
        'software' => [
            ['platform' => 'microsoft-store', 'region_code' => 'GLOBAL', 'os' => 'windows-11', 'editions' => ['1 Device / 1 Year', '5 Devices / 1 Year', 'Lifetime']],
            ['platform' => 'microsoft-store', 'region_code' => 'GLOBAL', 'os' => 'macos', 'editions' => ['1 Device / 1 Year']],
        ],
        'gift_card' => [
            ['platform' => 'steam', 'region_code' => 'US', 'os' => 'windows-10', 'editions' => ['$10', '$20', '$50', '$100']],
            ['platform' => 'steam', 'region_code' => 'GLOBAL', 'os' => 'windows-10', 'editions' => ['$10', '$20', '$50']],
            ['platform' => 'steam', 'region_code' => 'TR', 'os' => 'windows-10', 'editions' => ['100 TL', '500 TL', '1000 TL']],
        ],
    ];

    /** @var array<string, int|null> */
    private array $steamAppIds = [
        // Top 50 Games
        'cyberpunk-2077'          => 1091500, 'red-dead-redemption-2' => 1174180, 'grand-theft-auto-v' => 271590,
        'elden-ring'              => 1245620, 'baldur-s-gate-3' => 1086940, 'hogwarts-legacy' => 990080,
        'god-of-war'              => 1593500, 'starfield' => 1716740, 'counter-strike-2' => 730,
        'rainbow-six-siege'       => 359550, 'ea-sports-fc-24' => 2195250, 'forza-horizon-5' => 1551360,
        'hades'                   => 1145360, 'stardew-valley' => 413150, 'hollow-knight' => 367520,
        'the-witcher-3-wild-hunt' => 292030, 'resident-evil-4-remake' => 2050650, 'sekiro-shadows-die-twice' => 814380,
        'dead-space-remake'       => 1693980, 'street-fighter-6' => 1364780, 'tekken-8' => 1778820,
        'palworld'                => 1623730, 'helldivers-2' => 553850, 'lethal-company' => 1966720,
        'rust'                    => 252490, 'apex-legends' => 1172470, 'dota-2' => 570,
        'pubg-battlegrounds'      => 578080, 'monster-hunter-world' => 582010, 'terraria' => 105600,
        'garrys-mod'              => 4000, 'phasmophobia' => 739630, 'rimworld' => 294100,
        'hearts-of-iron-iv'       => 394360, 'civilization-vi' => 289070, 'cities-skylines' => 255710,
        'left-4-dead-2'           => 550, 'dead-by-daylight' => 381210, 'fallout-4' => 377160,
        'skyrim-special-edition'  => 489830, 'no-mans-sky' => 275850, 'dayz' => 221100,
        'ark-survival-ascended'   => 2399830, 'sea-of-thieves' => 1172620, 'payday-3' => 1272080,
        'cities-skylines-ii'      => 1259980, 'lies-of-p' => 1627720, 'mortal-kombat-1' => 1971870,
        'armored-core-vi'         => 1888160, 'remnant-ii' => 1282100,

        // Software & Others (null AppID sẽ dùng Fallback)
        'valorant'                 => null, 'diablo-iv' => null, 'minecraft' => null,
        'windows-11-pro'           => null, 'windows-10-pro' => null, 'microsoft-365-personal' => null,
        'adobe-creative-cloud'     => null, 'idm-internet-download-manager' => null,
        'kaspersky-total-security' => null, 'nordvpn' => null,
        'steam-wallet-card'        => null, 'xbox-game-pass-ultimate' => null, 'psn-gift-card' => null,
    ];

    /** @var array<string, string|null> */
    private array $thumbnailFallbackUrls = [
        'valorant'               => 'https://athenaposters.ca/wp-content/uploads/2023/01/EXR8837-Valorant-.jpeg',
        'diablo-iv'              => 'https://blz-contentstack-images.akamaized.net/v3/assets/blt9c12f249ac15c7ec/blt6d203657cd3df3a4/644bfda152a5c531d05cd7dd/D4_Launch_KeyArt_16_9_no_logos.jpg',
        'minecraft'              => 'https://assets.nintendo.com/image/upload/ar_16:9,c_lpad,w_1200/b_white/f_auto/q_auto/ncom/software/switch/70010000000964/811461b8d1cacf1f2280dc38522199bed3e2ec35bbfad25eb2eb5423d6a61763',
        'windows-11-pro'         => 'https://cdn-dynmedia-1.microsoft.com/is/image/microsoftcorp/Windows-11-Pro-Retail-Box-EN',
        'microsoft-365-personal' => 'https://cdn-dynmedia-1.microsoft.com/is/image/microsoftcorp/Microsoft-365-Personal-EN',
        'adobe-creative-cloud'   => 'https://skunkworks.africa/cdn/shop/files/creative-cloud-all-apps.png',
        'steam-wallet-card'      => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVwGoO5BOz4eSHKlckTuiFQmDoFWLoNQKcuQ&s',
    ];

    public function run(): void
    {
        $this->seedProducts();
    }

    protected function seedProducts(): void
    {
        $products = $this->getProductsData();
        $disk = config('filesystems.public_disk', 'public');
        $sellers = Seller::where('kyc_status', KycStatus::Approved)->get();
        $this->activeSellerIds = $sellers->pluck('id')->all();

        Storage::disk($disk)->makeDirectory('products/thumbnails');

        $platforms = Platform::where('status', GeneralStatus::Active)->get();
        $regions = Region::where('status', GeneralStatus::Active)->get();
        $oses = OperatingSystem::where('status', GeneralStatus::Active)->get();
        $allCategories = Category::all();

        foreach ($products as $productData) {
            $productSlug = Str::slug($productData['name']);
            $submittedBySellerId = $this->resolveSubmittedBySellerId($productData, $sellers);

            $createdAt = now()->subDays(random_int(1, 365))->subMinutes(random_int(1, 1440));

            $product = Product::firstOrCreate(
                ['slug' => $productSlug],
                [
                    'name'                   => $productData['name'],
                    'image_thumbnail_path'   => 'products/thumbnails/'.$productSlug.'.jpg',
                    'publisher'              => $productData['publisher'],
                    'developer'              => $productData['developer'],
                    'release_date'           => $productData['release_date'],
                    'description'            => $productData['description'],
                    'system_requirement'     => $productData['system_requirement'],
                    'submitted_by_seller_id' => $submittedBySellerId,
                    'approved_by'            => null,
                    'status'                 => GeneralStatus::Active,
                    'created_at'             => $createdAt,
                    'updated_at'             => $createdAt,
                ]
            );

            if (! empty($productData['categories'])) {
                $syncData = [];

                foreach ($productData['categories'] as $index => $catSlug) {
                    $category = $allCategories->firstWhere('slug', $catSlug);

                    if ($category) {
                        $syncData[$category->id] = [
                            'sort_order'  => $index,
                            'is_featured' => fake()->boolean(20),
                        ];
                    }
                }

                if (! empty($syncData)) {
                    $product->categories()->syncWithoutDetaching($syncData);
                }
            }

            $this->downloadProductThumbnail($productSlug, $disk);

            $priceRange = $this->convertPriceRangeToVnd($productData['price_range'] ?? [9.99, 59.99]);
            $templateKey = $productData['variant_template'] ?? 'steam_aaa';
            $variantsToCreate = $this->variantTemplates[$templateKey];

            $this->createVariants(
                $product,
                $variantsToCreate,
                $priceRange,
                $submittedBySellerId,
                $platforms,
                $regions,
                $oses,
                $createdAt
            );
        }
    }

    protected function createVariants(Product $product, array $variantConfigs, array $priceRange, ?int $submittedBySellerId, Collection $platforms, Collection $regions, Collection $oses, DateTimeInterface $createdAt): void
    {
        foreach ($variantConfigs as $variantConfig) {
            $platform = $platforms->firstWhere('slug', Str::slug($variantConfig['platform']));
            $region = $regions->firstWhere('flag_code', $variantConfig['region_code']);
            $os = $oses->firstWhere('slug', Str::slug($variantConfig['os']));

            if (! $platform || ! $region || ! $os) {
                continue;
            }

            $editions = $variantConfig['editions'] ?? ['Standard'];

            foreach ($editions as $edition) {
                $variant = ProductVariant::firstOrCreate([
                    'product_id'  => $product->id,
                    'region_id'   => $region->id,
                    'platform_id' => $platform->id,
                    'os_id'       => $os->id,
                    'edition'     => $edition,
                ], [
                    'status'     => ProductVariantStatus::Active,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $this->createListings($variant, $priceRange, $submittedBySellerId);
            }
        }
    }

    protected function createListings(ProductVariant $variant, array $priceRange, ?int $submittedBySellerId): void
    {
        $listingSellerIds = $this->resolveListingSellerIds($submittedBySellerId);

        foreach ($listingSellerIds as $sellerId) {
            $price = $this->randomVndPrice($priceRange);
            $status = $this->listingStatusForOwner($sellerId);
            $displayName = $this->makeListingDisplayName($variant);

            $listing = ProductListing::firstOrNew([
                'variant_id' => $variant->id,
                'seller_id'  => $sellerId,
            ]);

            $listing->fill([
                'display_name' => $listing->exists ? $listing->display_name : $displayName,
                'seller_id'    => $sellerId,
                'price'        => $price,
                'stock_count'  => 0,
                'status'       => $status,
            ]);

            $listing->setRelation('variant', $variant);

            if (! filled($listing->slug)) {
                $listing->slug = ProductListing::generateSlug($listing);
            }

            $createdAt = now()->subDays(random_int(1, 365))->subMinutes(random_int(1, 1440));

            $listing->timestamps = false;
            $listing->created_at = $listing->exists ? $listing->created_at : $createdAt;
            $listing->updated_at = $createdAt;
            $listing->save();
            $listing->timestamps = true;

            if ($listing->wasRecentlyCreated) {
                $this->createKeys($listing, $status, $createdAt);
            }
        }
    }

    /**
     * @return list<int|null>
     */
    protected function resolveListingSellerIds(?int $submittedBySellerId): array
    {
        if ($submittedBySellerId !== null) {
            return [$submittedBySellerId];
        }

        $sellerListingCount = fake()->randomElement([0, 1, 1, 2]);
        $sellerIds = $this->activeSellerIds === []
            ? []
            : collect($this->activeSellerIds)
                ->shuffle()
                ->take($sellerListingCount)
                ->values()
                ->all();

        array_unshift($sellerIds, null);

        return $sellerIds;
    }

    protected function listingStatusForOwner(?int $sellerId): ProductListingStatus
    {
        if ($sellerId === null) {
            return ProductListingStatus::Active;
        }

        return fake()->randomElement([
            ProductListingStatus::Active,
            ProductListingStatus::Active,
            ProductListingStatus::Active,
            ProductListingStatus::Pending,
            ProductListingStatus::Hidden,
        ]);
    }

    protected function makeListingDisplayName(ProductVariant $variant): ?string
    {
        if (! fake()->boolean(70)) {
            return null;
        }

        $productName = $variant->product?->name ?? 'Key';
        $region = $variant->region?->flag_code ?? 'GLOBAL';

        return "{$productName} - {$variant->edition} ({$region})";
    }

    protected function createKeys(ProductListing $listing, ProductListingStatus $status, DateTimeInterface $createdAt): void
    {
        $numKeys = match (true) {
            $listing->seller_id === null && $status === ProductListingStatus::Active => random_int(35, 90),
            $status === ProductListingStatus::Active                                 => random_int(10, 50),
            default                                                                  => random_int(2, 10),
        };

        for ($i = 0; $i < $numKeys; $i++) {
            $keyCode = $this->generateKeyCode();

            ProductKey::forceCreate([
                'listing_id' => $listing->id,
                'key_code'   => $keyCode,
                'key_hash'   => hash('sha256', $keyCode),
                'status'     => ProductKeyStatus::Available,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $listing->update([
            'stock_count' => $listing->keys()->where('status', ProductKeyStatus::Available)->count(),
        ]);
    }

    protected function generateKeyCode(): string
    {
        return strtoupper(Str::random(5).'-'.Str::random(5).'-'.Str::random(5));
    }

    protected function convertPriceRangeToVnd(array $priceRange): array
    {
        return [
            (int) (round((((float) $priceRange[0]) * $this->usdToVndRate) / 1000) * 1000),
            (int) (round((((float) $priceRange[1]) * $this->usdToVndRate) / 1000) * 1000),
        ];
    }

    protected function randomVndPrice(array $priceRange): int
    {
        $min = $priceRange[0];
        $max = $priceRange[1];

        return $max <= $min ? $min : (int) (round(random_int($min, $max) / 1000) * 1000);
    }

    protected function downloadProductThumbnail(string $slug, string $disk): void
    {
        $targetPath = "products/thumbnails/{$slug}.jpg";
        if (Storage::disk($disk)->exists($targetPath)) {
            return;
        }

        $steamAppId = $this->steamAppIds[$slug] ?? null;
        $url = $steamAppId
            ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$steamAppId}/library_600x900.jpg"
            : ($this->thumbnailFallbackUrls[$slug] ?? 'https://placehold.co/600x900/0f172a/ffffff?text='.urlencode(Str::headline(str_replace('-', ' ', $slug))));

        try {
            $response = Http::timeout(10)->retry(2, 200)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);

            if ($response->successful() && strlen($response->body()) > 1000) {
                Storage::disk($disk)->put($targetPath, $response->body());
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function resolveSubmittedBySellerId(array $productData, Collection $sellers): ?int
    {
        if (($productData['submitted_by'] ?? 'admin') === 'seller' && $sellers->isNotEmpty()) {
            return $sellers->random()->id;
        }

        return $productData['submitted_by_seller_id'] ?? null;
    }

    protected function getProductsData(): array
    {
        // Cấu hình chung cho System Requirements để tái sử dụng, giúp code bớt cồng kềnh
        $sysReqHigh = ['os' => 'Windows 10/11 64-bit', 'processor' => 'Intel Core i7 / AMD Ryzen 7', 'memory' => '16 GB RAM', 'graphics' => 'NVIDIA RTX 3060 / AMD RX 6700 XT', 'directx' => 'Version 12', 'storage' => '100 GB SSD'];
        $sysReqMed = ['os' => 'Windows 10 64-bit', 'processor' => 'Intel Core i5 / AMD Ryzen 5', 'memory' => '8 GB RAM', 'graphics' => 'NVIDIA GTX 1060 / AMD RX 580', 'directx' => 'Version 11', 'storage' => '50 GB'];
        $sysReqLow = ['os' => 'Windows 10 64-bit', 'processor' => 'Intel Core i3', 'memory' => '4 GB RAM', 'graphics' => 'NVIDIA GTX 660', 'directx' => 'Version 11', 'storage' => '20 GB'];

        return [
            // --- 50 GAMES ---
            ['name' => 'Cyberpunk 2077', 'publisher' => 'CD Projekt Red', 'developer' => 'CD Projekt Red', 'release_date' => '2020-12-10', 'description' => 'Cyberpunk 2077 is an open-world, action-adventure RPG set in the dark future of Night City.', 'categories' => ['action', 'rpg', 'open-world', 'story-rich'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Red Dead Redemption 2', 'publisher' => 'Rockstar Games', 'developer' => 'Rockstar Games', 'release_date' => '2019-11-05', 'description' => 'Winner of over 175 Game of the Year Awards and recipient of over 250 perfect scores.', 'categories' => ['action', 'open-world', 'adventure', 'story-rich'], 'price_range' => [19.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Grand Theft Auto V', 'publisher' => 'Rockstar Games', 'developer' => 'Rockstar North', 'release_date' => '2015-04-14', 'description' => 'Explore the stunning world of Los Santos and Blaine County.', 'categories' => ['action', 'open-world', 'multiplayer'], 'price_range' => [14.99, 29.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Elden Ring', 'publisher' => 'Bandai Namco', 'developer' => 'FromSoftware', 'release_date' => '2022-02-25', 'description' => 'THE NEW FANTASY ACTION RPG. Rise, Tarnished, and be guided by grace.', 'categories' => ['rpg', 'action', 'open-world', 'souls-like'], 'price_range' => [39.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Baldur\'s Gate 3', 'publisher' => 'Larian Studios', 'developer' => 'Larian Studios', 'release_date' => '2023-08-03', 'description' => 'Gather your party and return to the Forgotten Realms in a tale of fellowship.', 'categories' => ['rpg', 'strategy', 'turn-based-rpg', 'story-rich'], 'price_range' => [49.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Hogwarts Legacy', 'publisher' => 'Warner Bros.', 'developer' => 'Avalanche', 'release_date' => '2023-02-10', 'description' => 'Experience Hogwarts in the 1800s. Your legacy is what you make it.', 'categories' => ['rpg', 'adventure', 'open-world', 'magic'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'God of War', 'publisher' => 'PlayStation PC', 'developer' => 'Santa Monica', 'release_date' => '2022-01-14', 'description' => 'His vengeance against the Gods of Olympus years behind him, Kratos now lives as a man.', 'categories' => ['action', 'adventure', 'story-rich'], 'price_range' => [24.99, 49.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Starfield', 'publisher' => 'Bethesda', 'developer' => 'Bethesda Game Studios', 'release_date' => '2023-09-06', 'description' => 'Starfield is the first new universe in 25 years from Bethesda Game Studios.', 'categories' => ['rpg', 'open-world', 'sci-fi'], 'price_range' => [39.99, 69.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Counter-Strike 2', 'publisher' => 'Valve', 'developer' => 'Valve', 'release_date' => '2023-09-27', 'description' => 'For over two decades, Counter-Strike has offered an elite competitive experience.', 'categories' => ['fps', 'multiplayer', 'competitive', 'esports'], 'price_range' => [14.99, 14.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Rainbow Six Siege', 'publisher' => 'Ubisoft', 'developer' => 'Ubisoft Montreal', 'release_date' => '2015-12-01', 'description' => 'Master the art of destruction and gadgetry in Tom Clancy’s Rainbow Six Siege.', 'categories' => ['fps', 'multiplayer', 'tactical', 'esports'], 'price_range' => [9.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'EA SPORTS FC 24', 'publisher' => 'Electronic Arts', 'developer' => 'EA Vancouver', 'release_date' => '2023-09-29', 'description' => 'EA SPORTS FC 24 welcomes you to The World’s Game.', 'categories' => ['sports', 'football', 'multiplayer'], 'price_range' => [19.99, 69.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Forza Horizon 5', 'publisher' => 'Xbox Game Studios', 'developer' => 'Playground Games', 'release_date' => '2021-11-09', 'description' => 'Explore the vibrant open world landscapes of Mexico with limitless driving action.', 'categories' => ['racing', 'open-world', 'multiplayer'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Hades', 'publisher' => 'Supergiant Games', 'developer' => 'Supergiant Games', 'release_date' => '2020-09-17', 'description' => 'Defy the god of the dead as you hack and slash out of the Underworld.', 'categories' => ['action', 'rogue-like', 'indie'], 'price_range' => [12.49, 24.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Stardew Valley', 'publisher' => 'ConcernedApe', 'developer' => 'ConcernedApe', 'release_date' => '2016-02-26', 'description' => 'You\'ve inherited your grandfather\'s old farm plot in Stardew Valley.', 'categories' => ['simulation', 'farming', 'indie', 'rpg'], 'price_range' => [7.49, 14.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Hollow Knight', 'publisher' => 'Team Cherry', 'developer' => 'Team Cherry', 'release_date' => '2017-02-24', 'description' => 'Forge your own path in Hollow Knight! An epic action adventure.', 'categories' => ['action', 'metroidvania', 'indie', 'platformer'], 'price_range' => [7.49, 14.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'The Witcher 3: Wild Hunt', 'publisher' => 'CD Projekt Red', 'developer' => 'CD Projekt Red', 'release_date' => '2015-05-18', 'description' => 'You are Geralt of Rivia, mercenary monster slayer.', 'categories' => ['rpg', 'open-world', 'story-rich'], 'price_range' => [9.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Resident Evil 4 Remake', 'publisher' => 'Capcom', 'developer' => 'Capcom', 'release_date' => '2023-03-24', 'description' => 'Survival is just the beginning. Six years have passed since the biological disaster in Raccoon City.', 'categories' => ['action', 'horror', 'survival-horror'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Sekiro: Shadows Die Twice', 'publisher' => 'Activision', 'developer' => 'FromSoftware', 'release_date' => '2019-03-22', 'description' => 'Carve your own clever path to revenge in an all-new adventure.', 'categories' => ['action', 'souls-like', 'ninja'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Dead Space Remake', 'publisher' => 'Electronic Arts', 'developer' => 'Motive', 'release_date' => '2023-01-27', 'description' => 'The sci-fi survival horror classic returns, completely rebuilt.', 'categories' => ['horror', 'action', 'sci-fi'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Street Fighter 6', 'publisher' => 'Capcom', 'developer' => 'Capcom', 'release_date' => '2023-06-02', 'description' => 'Here comes Capcom’s newest challenger! Street Fighter 6 represents the next evolution.', 'categories' => ['fighting', 'action', 'competitive'], 'price_range' => [39.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Tekken 8', 'publisher' => 'Bandai Namco', 'developer' => 'Bandai Namco Studios', 'release_date' => '2024-01-26', 'description' => 'Get ready for the next chapter in the legendary fighting game franchise.', 'categories' => ['fighting', 'action', 'multiplayer'], 'price_range' => [49.99, 69.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Palworld', 'publisher' => 'Pocketpair', 'developer' => 'Pocketpair', 'release_date' => '2024-01-19', 'description' => 'Fight, farm, build and work alongside mysterious creatures called "Pals".', 'categories' => ['survival', 'open-world', 'multiplayer', 'creature-collector'], 'price_range' => [26.99, 29.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqMed],
            ['name' => 'Helldivers 2', 'publisher' => 'PlayStation PC', 'developer' => 'Arrowhead', 'release_date' => '2024-02-08', 'description' => 'The Galaxy’s Last Line of Offence. Enlist in the Helldivers and join the fight.', 'categories' => ['action', 'co-op', 'tps', 'multiplayer'], 'price_range' => [39.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Lethal Company', 'publisher' => 'Zeekerss', 'developer' => 'Zeekerss', 'release_date' => '2023-10-24', 'description' => 'A co-op horror about scavenging abandoned moons to sell scrap to the Company.', 'categories' => ['horror', 'co-op', 'indie', 'survival'], 'price_range' => [7.99, 9.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Rust', 'publisher' => 'Facepunch Studios', 'developer' => 'Facepunch Studios', 'release_date' => '2018-02-08', 'description' => 'The only aim in Rust is to survive. Everything wants you to die.', 'categories' => ['survival', 'multiplayer', 'open-world', 'crafting'], 'price_range' => [19.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Apex Legends', 'publisher' => 'Electronic Arts', 'developer' => 'Respawn', 'release_date' => '2020-11-05', 'description' => 'Conquer with character in Apex Legends, a free-to-play Hero shooter.', 'categories' => ['fps', 'battle-royale', 'multiplayer'], 'price_range' => [9.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Dota 2', 'publisher' => 'Valve', 'developer' => 'Valve', 'release_date' => '2013-07-09', 'description' => 'Every day, millions of players worldwide enter battle as one of over a hundred Dota heroes.', 'categories' => ['moba', 'strategy', 'multiplayer', 'esports'], 'price_range' => [4.99, 29.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'PUBG: BATTLEGROUNDS', 'publisher' => 'KRAFTON', 'developer' => 'KRAFTON', 'release_date' => '2017-12-21', 'description' => 'Land, loot, survive! Play PUBG: BATTLEGROUNDS for free.', 'categories' => ['fps', 'battle-royale', 'survival'], 'price_range' => [14.99, 29.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Monster Hunter: World', 'publisher' => 'Capcom', 'developer' => 'Capcom', 'release_date' => '2018-08-09', 'description' => 'Welcome to a new world! Take on the role of a hunter and slay ferocious monsters.', 'categories' => ['action', 'rpg', 'co-op', 'hunting'], 'price_range' => [14.99, 29.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Terraria', 'publisher' => 'Re-Logic', 'developer' => 'Re-Logic', 'release_date' => '2011-05-16', 'description' => 'Dig, fight, explore, build! Nothing is impossible in this action-packed adventure game.', 'categories' => ['sandbox', 'survival', 'indie', 'adventure'], 'price_range' => [4.99, 9.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Garry\'s Mod', 'publisher' => 'Valve', 'developer' => 'Facepunch Studios', 'release_date' => '2006-11-29', 'description' => 'Garry\'s Mod is a physics sandbox. There aren\'t any predefined aims or goals.', 'categories' => ['sandbox', 'multiplayer', 'indie', 'simulation'], 'price_range' => [4.99, 9.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Phasmophobia', 'publisher' => 'Kinetic Games', 'developer' => 'Kinetic Games', 'release_date' => '2020-09-18', 'description' => 'Phasmophobia is a 4 player online co-op psychological horror.', 'categories' => ['horror', 'co-op', 'indie', 'vr'], 'price_range' => [11.99, 13.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqMed],
            ['name' => 'RimWorld', 'publisher' => 'Ludeon Studios', 'developer' => 'Ludeon Studios', 'release_date' => '2018-10-17', 'description' => 'A sci-fi colony sim driven by an intelligent AI story generator.', 'categories' => ['simulation', 'strategy', 'base-building', 'indie'], 'price_range' => [24.99, 34.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Hearts of Iron IV', 'publisher' => 'Paradox', 'developer' => 'Paradox', 'release_date' => '2016-06-06', 'description' => 'Victory is at your fingertips! Your ability to lead your nation is your supreme weapon.', 'categories' => ['grand-strategy', 'history', 'simulation'], 'price_range' => [9.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Civilization VI', 'publisher' => '2K', 'developer' => 'Firaxis Games', 'release_date' => '2016-10-21', 'description' => 'Civilization VI offers new ways to interact with your world, expand your empire across the map.', 'categories' => ['strategy', 'turn-based-strategy', 'history'], 'price_range' => [5.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Cities: Skylines', 'publisher' => 'Paradox', 'developer' => 'Colossal Order', 'release_date' => '2015-03-10', 'description' => 'Cities: Skylines is a modern take on the classic city simulation.', 'categories' => ['simulation', 'city-builder', 'strategy'], 'price_range' => [8.99, 29.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Left 4 Dead 2', 'publisher' => 'Valve', 'developer' => 'Valve', 'release_date' => '2009-11-17', 'description' => 'Set in the zombie apocalypse, Left 4 Dead 2 (L4D2) is the highly anticipated sequel.', 'categories' => ['fps', 'zombie', 'co-op', 'action'], 'price_range' => [1.99, 9.99], 'variant_template' => 'steam_indie', 'system_requirement' => $sysReqLow],
            ['name' => 'Dead by Daylight', 'publisher' => 'Behaviour Interactive', 'developer' => 'Behaviour', 'release_date' => '2016-06-14', 'description' => 'Dead by Daylight is a multiplayer (4vs1) horror game where one player takes on the role of the savage Killer.', 'categories' => ['horror', 'survival', 'multiplayer', 'co-op'], 'price_range' => [9.99, 19.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Fallout 4', 'publisher' => 'Bethesda', 'developer' => 'Bethesda Game Studios', 'release_date' => '2015-11-10', 'description' => 'Bethesda Game Studios welcomes you to Fallout 4, their most ambitious game ever.', 'categories' => ['rpg', 'open-world', 'post-apocalyptic', 'action'], 'price_range' => [9.99, 19.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Skyrim Special Edition', 'publisher' => 'Bethesda', 'developer' => 'Bethesda Game Studios', 'release_date' => '2016-10-28', 'description' => 'Winner of more than 200 Game of the Year Awards, Skyrim Special Edition brings the epic fantasy to life.', 'categories' => ['rpg', 'open-world', 'fantasy', 'adventure'], 'price_range' => [9.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'No Man\'s Sky', 'publisher' => 'Hello Games', 'developer' => 'Hello Games', 'release_date' => '2016-08-12', 'description' => 'No Man\'s Sky is a game about exploration and survival in an infinite procedurally generated universe.', 'categories' => ['open-world', 'space', 'exploration', 'survival'], 'price_range' => [29.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'DayZ', 'publisher' => 'Bohemia Interactive', 'developer' => 'Bohemia Interactive', 'release_date' => '2018-12-13', 'description' => 'How long can you survive a post-apocalyptic world? A land overrun with an infected "zombie" population.', 'categories' => ['survival', 'zombie', 'multiplayer', 'open-world'], 'price_range' => [22.49, 44.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'ARK: Survival Ascended', 'publisher' => 'Studio Wildcard', 'developer' => 'Studio Wildcard', 'release_date' => '2023-10-26', 'description' => 'Respawn into a new dinosaur survival experience beyond your wildest dreams.', 'categories' => ['survival', 'dinosaurs', 'open-world', 'multiplayer'], 'price_range' => [39.99, 44.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Sea of Thieves', 'publisher' => 'Xbox Game Studios', 'developer' => 'Rare', 'release_date' => '2020-06-03', 'description' => 'Sea of Thieves offers the essential pirate experience, from sailing and fighting to exploring and looting.', 'categories' => ['adventure', 'multiplayer', 'pirates', 'open-world'], 'price_range' => [19.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'PAYDAY 3', 'publisher' => 'Deep Silver', 'developer' => 'Starbreeze', 'release_date' => '2023-09-21', 'description' => 'PAYDAY 3 is the explosive sequel to one of the most popular co-op shooters of the past decade.', 'categories' => ['action', 'fps', 'co-op', 'heist'], 'price_range' => [19.99, 39.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqMed],
            ['name' => 'Cities: Skylines II', 'publisher' => 'Paradox', 'developer' => 'Colossal Order', 'release_date' => '2023-10-24', 'description' => 'Raise a city from the ground up and transform it into a thriving metropolis.', 'categories' => ['simulation', 'city-builder', 'strategy'], 'price_range' => [39.99, 49.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Lies of P', 'publisher' => 'NEOWIZ', 'developer' => 'NEOWIZ', 'release_date' => '2023-09-18', 'description' => 'Lies of P is a thrilling soulslike that takes the story of Pinocchio, turns it on its head.', 'categories' => ['action', 'rpg', 'souls-like', 'dark-fantasy'], 'price_range' => [41.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Mortal Kombat 1', 'publisher' => 'Warner Bros.', 'developer' => 'NetherRealm', 'release_date' => '2023-09-19', 'description' => 'Discover a reborn Mortal Kombat Universe created by the Fire God Liu Kang.', 'categories' => ['fighting', 'action', 'multiplayer', 'gore'], 'price_range' => [49.99, 69.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Armored Core VI', 'publisher' => 'Bandai Namco', 'developer' => 'FromSoftware', 'release_date' => '2023-08-24', 'description' => 'A new action game based on the concept of the ARMORED CORE series.', 'categories' => ['action', 'mechs', 'sci-fi'], 'price_range' => [39.99, 59.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],
            ['name' => 'Remnant II', 'publisher' => 'Arc Games', 'developer' => 'Gunfire Games', 'release_date' => '2023-07-25', 'description' => 'REMNANT II pits survivors of humanity against new deadly creatures and god-like bosses.', 'categories' => ['action', 'rpg', 'co-op', 'souls-like'], 'price_range' => [29.99, 49.99], 'variant_template' => 'steam_aaa', 'system_requirement' => $sysReqHigh],

            // --- 10 PHẦN MỀM & THẺ NẠP ---
            ['name' => 'Windows 11 Pro', 'publisher' => 'Microsoft', 'developer' => 'Microsoft', 'release_date' => '2021-10-05', 'description' => 'The latest operating system for professionals, offering advanced security and productivity tools.', 'categories' => ['operating-system', 'software'], 'price_range' => [19.99, 199.99], 'variant_template' => 'software', 'system_requirement' => $sysReqMed],
            ['name' => 'Windows 10 Pro', 'publisher' => 'Microsoft', 'developer' => 'Microsoft', 'release_date' => '2015-07-29', 'description' => 'Familiar and better than ever. Perfect for business and power users.', 'categories' => ['operating-system', 'software'], 'price_range' => [14.99, 139.99], 'variant_template' => 'software', 'system_requirement' => $sysReqLow],
            ['name' => 'Microsoft 365 Personal', 'publisher' => 'Microsoft', 'developer' => 'Microsoft', 'release_date' => '2020-04-21', 'description' => 'Premium Office apps (Word, Excel, PowerPoint) and 1 TB cloud storage.', 'categories' => ['software', 'office-work'], 'price_range' => [49.99, 69.99], 'variant_template' => 'software', 'system_requirement' => $sysReqLow],
            ['name' => 'Adobe Creative Cloud', 'publisher' => 'Adobe', 'developer' => 'Adobe', 'release_date' => '2023-01-01', 'description' => 'All Adobe apps including Photoshop, Premiere Pro, Illustrator, and more for 1 Year.', 'categories' => ['software', 'design-illustration', 'video-editing'], 'price_range' => [199.99, 399.99], 'variant_template' => 'software', 'system_requirement' => $sysReqHigh],
            ['name' => 'IDM Internet Download Manager', 'publisher' => 'Tonec', 'developer' => 'Tonec', 'release_date' => '2023-01-01', 'description' => 'Lifetime license for the fastest download manager on the market.', 'categories' => ['software', 'utilities'], 'price_range' => [11.99, 24.99], 'variant_template' => 'software', 'system_requirement' => $sysReqLow],
            ['name' => 'Kaspersky Total Security', 'publisher' => 'Kaspersky', 'developer' => 'Kaspersky', 'release_date' => '2023-01-01', 'description' => 'Ultimate PC protection against malware, ransomware, and hackers.', 'categories' => ['antivirus-security', 'software'], 'price_range' => [14.99, 39.99], 'variant_template' => 'software', 'system_requirement' => $sysReqLow],
            ['name' => 'NordVPN', 'publisher' => 'Nord Security', 'developer' => 'Nord Security', 'release_date' => '2023-01-01', 'description' => 'Secure your internet with lightning-fast VPN connections.', 'categories' => ['software', 'networking'], 'price_range' => [29.99, 89.99], 'variant_template' => 'software', 'system_requirement' => $sysReqLow],
            ['name' => 'Steam Wallet Card', 'publisher' => 'Valve', 'developer' => 'Valve', 'release_date' => '2010-01-01', 'description' => 'Add funds to your Steam Wallet easily and instantly to purchase any game.', 'categories' => ['steam-wallet', 'gift-cards'], 'price_range' => [10.00, 100.00], 'variant_template' => 'gift_card', 'system_requirement' => $sysReqLow],
            ['name' => 'Xbox Game Pass Ultimate', 'publisher' => 'Microsoft', 'developer' => 'Microsoft', 'release_date' => '2019-01-01', 'description' => 'Play hundreds of high-quality games on PC, console, and cloud.', 'categories' => ['subscriptions', 'gift-cards', 'xbox-live'], 'price_range' => [9.99, 14.99], 'variant_template' => 'software', 'system_requirement' => $sysReqMed],
            ['name' => 'PSN Gift Card', 'publisher' => 'Sony', 'developer' => 'Sony', 'release_date' => '2010-01-01', 'description' => 'Add funds to your PlayStation Network account to buy digital games and DLCs.', 'categories' => ['psn-card', 'gift-cards'], 'price_range' => [10.00, 100.00], 'variant_template' => 'gift_card', 'system_requirement' => $sysReqLow],
        ];
    }
}
