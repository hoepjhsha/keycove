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
        $slug = Str::slug($name).'-'.Str::lower(Str::random(8));

        return [
            'parent_id' => null,
            'name'      => $name,
            'slug'      => $slug,
            'status'    => GeneralStatus::Active,
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
            // 1. HÀNH ĐỘNG (ACTION)
            ['id' => 1, 'name' => 'Hành động (Action)', 'slug' => 'action', 'parent_name' => null],
            ['id' => 2, 'name' => 'Bắn súng góc nhìn thứ nhất (FPS)', 'slug' => 'fps', 'parent_name' => 'Hành động (Action)'],
            ['id' => 3, 'name' => 'Bắn súng góc nhìn thứ ba (TPS)', 'slug' => 'tps', 'parent_name' => 'Hành động (Action)'],
            ['id' => 4, 'name' => 'Đối kháng (Fighting)', 'slug' => 'fighting', 'parent_name' => 'Hành động (Action)'],
            ['id' => 5, 'name' => 'Chặt chém (Hack and Slash)', 'slug' => 'hack-and-slash', 'parent_name' => 'Hành động (Action)'],
            ['id' => 6, 'name' => 'Đi cảnh (Platformer)', 'slug' => 'platformer', 'parent_name' => 'Hành động (Action)'],
            ['id' => 7, 'name' => 'Bắn súng cuộn cảnh (Shoot \'em up)', 'slug' => 'shoot-em-up', 'parent_name' => 'Hành động (Action)'],
            ['id' => 8, 'name' => 'Sinh tồn hành động', 'slug' => 'action-survival', 'parent_name' => 'Hành động (Action)'],
            ['id' => 9, 'name' => 'Battle Royale', 'slug' => 'battle-royale', 'parent_name' => 'Hành động (Action)'],

            // 2. PHIÊU LƯU (ADVENTURE)
            ['id' => 10, 'name' => 'Phiêu lưu (Adventure)', 'slug' => 'adventure', 'parent_name' => null],
            ['id' => 11, 'name' => 'Trỏ và Nhấp (Point & Click)', 'slug' => 'point-and-click', 'parent_name' => 'Phiêu lưu (Adventure)'],
            ['id' => 12, 'name' => 'Cốt truyện (Story Rich)', 'slug' => 'story-rich', 'parent_name' => 'Phiêu lưu (Adventure)'],
            ['id' => 13, 'name' => 'Tiểu thuyết trực quan (Visual Novel)', 'slug' => 'visual-novel', 'parent_name' => 'Phiêu lưu (Adventure)'],
            ['id' => 14, 'name' => 'Metroidvania', 'slug' => 'metroidvania', 'parent_name' => 'Phiêu lưu (Adventure)'],
            ['id' => 15, 'name' => 'Tìm đồ vật (Hidden Object)', 'slug' => 'hidden-object', 'parent_name' => 'Phiêu lưu (Adventure)'],

            // 3. NHẬP VAI (RPG)
            ['id' => 16, 'name' => 'Nhập vai (RPG)', 'slug' => 'rpg', 'parent_name' => null],
            ['id' => 17, 'name' => 'Hành động nhập vai (Action RPG)', 'slug' => 'action-rpg', 'parent_name' => 'Nhập vai (RPG)'],
            ['id' => 18, 'name' => 'Nhập vai Nhật Bản (JRPG)', 'slug' => 'jrpg', 'parent_name' => 'Nhập vai (RPG)'],
            ['id' => 19, 'name' => 'MMORPG', 'slug' => 'mmorpg', 'parent_name' => 'Nhập vai (RPG)'],
            ['id' => 20, 'name' => 'Rogue-like / Rogue-lite', 'slug' => 'rogue-like', 'parent_name' => 'Nhập vai (RPG)'],
            ['id' => 21, 'name' => 'Theo lượt (Turn-Based RPG)', 'slug' => 'turn-based-rpg', 'parent_name' => 'Nhập vai (RPG)'],
            ['id' => 22, 'name' => 'Nhập vai chiến thuật (Strategy RPG)', 'slug' => 'strategy-rpg', 'parent_name' => 'Nhập vai (RPG)'],

            // 4. CHIẾN THUẬT (STRATEGY)
            ['id' => 23, 'name' => 'Chiến thuật (Strategy)', 'slug' => 'strategy', 'parent_name' => null],
            ['id' => 24, 'name' => 'Thời gian thực (RTS)', 'slug' => 'rts', 'parent_name' => 'Chiến thuật (Strategy)'],
            ['id' => 25, 'name' => 'Chiến thuật theo lượt (TBS)', 'slug' => 'tbs', 'parent_name' => 'Chiến thuật (Strategy)'],
            ['id' => 26, 'name' => 'Thủ thành (Tower Defense)', 'slug' => 'tower-defense', 'parent_name' => 'Chiến thuật (Strategy)'],
            ['id' => 27, 'name' => 'Đại chiến lược (Grand Strategy)', 'slug' => 'grand-strategy', 'parent_name' => 'Chiến thuật (Strategy)'],
            ['id' => 28, 'name' => 'Xây dựng thành phố (City Builder)', 'slug' => 'city-builder', 'parent_name' => 'Chiến thuật (Strategy)'],
            ['id' => 29, 'name' => '4X (Khám phá & Mở rộng)', 'slug' => '4x', 'parent_name' => 'Chiến thuật (Strategy)'],
            ['id' => 30, 'name' => 'Thẻ bài & Cờ bàn (Card & Board)', 'slug' => 'card-board', 'parent_name' => 'Chiến thuật (Strategy)'],

            // 5. MÔ PHỎNG (SIMULATION)
            ['id' => 31, 'name' => 'Mô phỏng (Simulation)', 'slug' => 'simulation', 'parent_name' => null],
            ['id' => 32, 'name' => 'Mô phỏng cuộc sống (Life Sim)', 'slug' => 'life-simulation', 'parent_name' => 'Mô phỏng (Simulation)'],
            ['id' => 33, 'name' => 'Nông trại & Chế tạo (Farming)', 'slug' => 'farming', 'parent_name' => 'Mô phỏng (Simulation)'],
            ['id' => 34, 'name' => 'Bay & Không gian (Flight & Space)', 'slug' => 'flight-space', 'parent_name' => 'Mô phỏng (Simulation)'],
            ['id' => 35, 'name' => 'Lái xe & Vận tải (Vehicle)', 'slug' => 'vehicle-sim', 'parent_name' => 'Mô phỏng (Simulation)'],
            ['id' => 36, 'name' => 'Quản lý doanh nghiệp (Management)', 'slug' => 'management', 'parent_name' => 'Mô phỏng (Simulation)'],

            // 6. THỂ THAO & ĐUA XE (SPORTS & RACING)
            ['id' => 37, 'name' => 'Thể thao & Đua xe', 'slug' => 'sports-racing', 'parent_name' => null],
            ['id' => 38, 'name' => 'Đua xe Arcade', 'slug' => 'arcade-racing', 'parent_name' => 'Thể thao & Đua xe'],
            ['id' => 39, 'name' => 'Đua xe mô phỏng (Sim Racing)', 'slug' => 'sim-racing', 'parent_name' => 'Thể thao & Đua xe'],
            ['id' => 40, 'name' => 'Bóng đá (Football/Soccer)', 'slug' => 'football', 'parent_name' => 'Thể thao & Đua xe'],
            ['id' => 41, 'name' => 'Bóng rổ (Basketball)', 'slug' => 'basketball', 'parent_name' => 'Thể thao & Đua xe'],
            ['id' => 42, 'name' => 'Thể thao điện tử (eSports)', 'slug' => 'esports', 'parent_name' => 'Thể thao & Đua xe'],
            ['id' => 43, 'name' => 'Câu cá & Săn bắn', 'slug' => 'fishing-hunting', 'parent_name' => 'Thể thao & Đua xe'],

            // 7. KINH DỊ (HORROR)
            ['id' => 44, 'name' => 'Kinh dị (Horror)', 'slug' => 'horror', 'parent_name' => null],
            ['id' => 45, 'name' => 'Kinh dị sinh tồn (Survival Horror)', 'slug' => 'survival-horror', 'parent_name' => 'Kinh dị (Horror)'],
            ['id' => 46, 'name' => 'Kinh dị tâm lý (Psychological)', 'slug' => 'psychological-horror', 'parent_name' => 'Kinh dị (Horror)'],
            ['id' => 47, 'name' => 'Kinh dị Zombie', 'slug' => 'zombie-horror', 'parent_name' => 'Kinh dị (Horror)'],

            // 8. CÁC THỂ LOẠI KHÁC & CHỦ ĐỀ
            ['id' => 48, 'name' => 'Giải đố (Puzzle)', 'slug' => 'puzzle', 'parent_name' => null],
            ['id' => 49, 'name' => 'Âm nhạc & Nhịp điệu (Rhythm)', 'slug' => 'rhythm', 'parent_name' => null],
            ['id' => 50, 'name' => 'Độc lập (Indie)', 'slug' => 'indie', 'parent_name' => null],
            ['id' => 51, 'name' => 'Anime', 'slug' => 'anime', 'parent_name' => null],

            // 9. THEO LỐI CHƠI & KHÔNG GIAN
            ['id' => 52, 'name' => 'Thế giới mở (Open World)', 'slug' => 'open-world', 'parent_name' => null],
            ['id' => 53, 'name' => 'Thế giới mở sinh tồn', 'slug' => 'open-world-survival', 'parent_name' => 'Thế giới mở (Open World)'],
            ['id' => 54, 'name' => 'Hộp cát (Sandbox)', 'slug' => 'sandbox', 'parent_name' => 'Thế giới mở (Open World)'],

            ['id' => 55, 'name' => 'Nhiều người chơi (Multiplayer)', 'slug' => 'multiplayer', 'parent_name' => null],
            ['id' => 56, 'name' => 'Chơi cùng nhau (Co-op)', 'slug' => 'co-op', 'parent_name' => 'Nhiều người chơi (Multiplayer)'],
            ['id' => 57, 'name' => 'Chia màn hình (Split Screen)', 'slug' => 'split-screen', 'parent_name' => 'Nhiều người chơi (Multiplayer)'],
            ['id' => 58, 'name' => 'MOBA', 'slug' => 'moba', 'parent_name' => 'Nhiều người chơi (Multiplayer)'],

            // 10. PHẦN MỀM & TIỆN ÍCH KHÁC (Dành cho web key)
            ['id' => 59, 'name' => 'Phần mềm (Software)', 'slug' => 'software', 'parent_name' => null],
            ['id' => 60, 'name' => 'Hệ điều hành', 'slug' => 'operating-system', 'parent_name' => 'Phần mềm (Software)'],
            ['id' => 61, 'name' => 'Phát triển & Lập trình', 'slug' => 'development', 'parent_name' => 'Phần mềm (Software)'],
            ['id' => 62, 'name' => 'Đồ họa & Thiết kế', 'slug' => 'design-illustration', 'parent_name' => 'Phần mềm (Software)'],
            ['id' => 63, 'name' => 'Xử lý Âm thanh & Video', 'slug' => 'audio-video-production', 'parent_name' => 'Phần mềm (Software)'],
            ['id' => 64, 'name' => 'Bảo mật & Diệt Virus', 'slug' => 'antivirus-security', 'parent_name' => 'Phần mềm (Software)'],

            // 11. THẺ QUÀ TẶNG & GÓI ĐĂNG KÝ
            ['id' => 65, 'name' => 'Thẻ quà tặng (Gift Cards)', 'slug' => 'gift-cards', 'parent_name' => null],
            ['id' => 66, 'name' => 'Steam Wallet', 'slug' => 'steam-wallet', 'parent_name' => 'Thẻ quà tặng (Gift Cards)'],
            ['id' => 67, 'name' => 'PlayStation Network (PSN)', 'slug' => 'psn-card', 'parent_name' => 'Thẻ quà tặng (Gift Cards)'],
            ['id' => 68, 'name' => 'Xbox Live & Game Pass', 'slug' => 'xbox-card', 'parent_name' => 'Thẻ quà tặng (Gift Cards)'],
            ['id' => 69, 'name' => 'Nintendo eShop', 'slug' => 'nintendo-card', 'parent_name' => 'Thẻ quà tặng (Gift Cards)'],
            ['id' => 70, 'name' => 'Apple / Google Play', 'slug' => 'mobile-card', 'parent_name' => 'Thẻ quà tặng (Gift Cards)'],
            ['id' => 71, 'name' => 'Gói Đăng ký (Subscriptions)', 'slug' => 'subscriptions', 'parent_name' => 'Thẻ quà tặng (Gift Cards)'],
        ];

        $parentMap = [];

        // First pass: create parent categories (parent_name = null)
        foreach ($categories as $cat) {
            if ($cat['parent_name'] === null) {
                $created = Category::firstOrCreate(
                    ['slug' => $cat['slug']], //
                    [
                        'name'   => $cat['name'], // [cite: 3]
                        'status' => GeneralStatus::Active, // [cite: 3]
                    ]
                );
                $parentMap[$cat['name']] = $created->id; // [cite: 3]
            }
        }

        // Second pass: create child categories with parent_id
        foreach ($categories as $cat) {
            if ($cat['parent_name'] !== null) {
                Category::firstOrCreate(
                    ['slug' => $cat['slug']], // [cite: 3]
                    [
                        'name'      => $cat['name'], // [cite: 3]
                        'parent_id' => $parentMap[$cat['parent_name']], // [cite: 3]
                        'status'    => GeneralStatus::Active, // [cite: 3]
                    ]
                );
            }
        }
    }
}
