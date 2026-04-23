<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAdmins();
        $this->createSellers();
        $this->createBuyers();
    }

    protected function createAdmins(): void
    {
        $adminData = [
            [
                'username'   => 'admin_nguyen',
                'email'      => 'nguyen@keycove.com',
                'first_name' => 'Minh',
                'last_name'  => 'Nguyen',
            ],
            [
                'username'   => 'admin_tran',
                'email'      => 'tran@keycove.com',
                'first_name' => 'Lan',
                'last_name'  => 'Tran',
            ],
            [
                'username'   => 'admin_le',
                'email'      => 'le@keycove.com',
                'first_name' => 'Duc',
                'last_name'  => 'Le',
            ],
        ];

        foreach ($adminData as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'password' => bcrypt('password'),
                    'role'     => UserRole::Admin,
                    'status'   => UserStatus::Active,
                ]
            );
        }
    }

    protected function createSellers(): void
    {
        $sellerData = [
            [
                'username'       => 'gamekey_store',
                'email'          => 'gamekey@store.com',
                'first_name'     => 'Hung',
                'last_name'      => 'Pham',
                'shop_name'      => 'GameKey Store VN',
                'kyc_status'     => KycStatus::Approved,
                'wallet_balance' => 15420.50,
                'wallet_holding' => 2340.00,
            ],
            [
                'username'       => 'digital_hub',
                'email'          => 'digital@hub.com',
                'first_name'     => 'Mai',
                'last_name'      => 'Hoang',
                'shop_name'      => 'Digital Keys Hub',
                'kyc_status'     => KycStatus::Approved,
                'wallet_balance' => 8750.25,
                'wallet_holding' => 1200.00,
            ],
            [
                'username'       => 'software_depot',
                'email'          => 'software@depot.com',
                'first_name'     => 'Tuan',
                'last_name'      => 'Vo',
                'shop_name'      => 'Software Depot',
                'kyc_status'     => KycStatus::Approved,
                'wallet_balance' => 32100.00,
                'wallet_holding' => 4500.00,
            ],
            [
                'username'       => 'keymaster_asia',
                'email'          => 'keymaster@asia.com',
                'first_name'     => 'Hoa',
                'last_name'      => 'Do',
                'shop_name'      => 'Keymaster Asia',
                'kyc_status'     => KycStatus::Pending,
                'wallet_balance' => 1250.75,
                'wallet_holding' => 450.00,
            ],
        ];

        foreach ($sellerData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'password' => bcrypt('password'),
                    'role'     => UserRole::Seller,
                    'status'   => UserStatus::Active,
                ]
            );

            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name'   => $data['first_name'],
                    'last_name'    => $data['last_name'],
                    'gender'       => fake()->randomElement([Gender::Male, Gender::Female]),
                    'phone_number' => fake()->numerify('09########'),
                    'avatar'       => 'avatars/seller_'.fake()->numberBetween(1, 5).'.jpg',
                    'bio'          => 'Trusted seller on KeyCove. Fast delivery and competitive prices.',
                ]
            );

            $frontImage = 'sellers/kyc/front_'.fake()->uuid().'.jpg';
            $backImage = 'sellers/kyc/back_'.fake()->uuid().'.jpg';

            $disk = config('filesystems.default');
            Storage::disk($disk)->makeDirectory('sellers/kyc');
            Storage::disk($disk)->put($frontImage, file_get_contents('https://placehold.co/800x500/EEE/31343C/png?text=Front+ID+Card'));
            Storage::disk($disk)->put($backImage, file_get_contents('https://placehold.co/800x500/EEE/31343C/png?text=Back+ID+Card'));

            $seller = Seller::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name'        => $data['shop_name'],
                    'cccd_number'      => fake()->numerify('############'),
                    'cccd_front_image' => $frontImage,
                    'cccd_back_image'  => $backImage,
                    'kyc_status'       => $data['kyc_status'],
                    'created_at'       => now()->subDays(random_int(1, 30)),
                    'updated_at'       => now()->subDays(random_int(1, 5)),
                ]
            );

            Wallet::firstOrCreate(
                ['seller_id' => $seller->id],
                [
                    'balance' => $data['wallet_balance'],
                    'holding' => $data['wallet_holding'],
                ]
            );
        }
    }

    protected function createBuyers(): void
    {
        $buyerData = [
            ['username' => 'gamer_vn', 'email' => 'gamer@example.com', 'first_name' => 'Long', 'last_name' => 'Nguyen'],
            ['username' => 'tech_enthusiast', 'email' => 'tech@example.com', 'first_name' => 'Hoa', 'last_name' => 'Tran'],
            ['username' => 'software_fan', 'email' => 'software@example.com', 'first_name' => 'Minh', 'last_name' => 'Le'],
            ['username' => 'bargain_hunter', 'email' => 'bargain@example.com', 'first_name' => 'Lan', 'last_name' => 'Pham'],
            ['username' => 'steam_collector', 'email' => 'steam@example.com', 'first_name' => 'Duc', 'last_name' => 'Hoang'],
            ['username' => 'indie_lover', 'email' => 'indie@example.com', 'first_name' => 'Mai', 'last_name' => 'Vo'],
            ['username' => 'rpg_master', 'email' => 'rpg@example.com', 'first_name' => 'Tuan', 'last_name' => 'Do'],
            ['username' => 'fps_pro', 'email' => 'fps@example.com', 'first_name' => 'Hung', 'last_name' => 'Bui'],
            ['username' => 'casual_player', 'email' => 'casual@example.com', 'first_name' => 'Anh', 'last_name' => 'Pham'],
            ['username' => 'deal_seeker', 'email' => 'deals@example.com', 'first_name' => 'Tam', 'last_name' => 'Trinh'],
        ];

        foreach ($buyerData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'password' => bcrypt('password'),
                    'role'     => UserRole::User,
                    'status'   => UserStatus::Active,
                ]
            );

            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name'   => $data['first_name'],
                    'last_name'    => $data['last_name'],
                    'gender'       => fake()->randomElement([Gender::Male, Gender::Female]),
                    'phone_number' => fake()->optional()->numerify('09########'),
                    'avatar'       => 'avatars/user_'.fake()->numberBetween(1, 10).'.jpg',
                ]
            );
        }
    }
}
