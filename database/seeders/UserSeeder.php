<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Cart;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSellers();
        $this->createBuyers();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function sellerData(): array
    {
        return [
            [
                'username'   => 'khanggame_vn',
                'email'      => 'khang.nguyen@keycove.vn',
                'first_name' => 'Khang',
                'last_name'  => 'Nguyen',
                'shop_name'  => 'Khang Game Key',
            ],
            [
                'username'   => 'minhanh_store',
                'email'      => 'minh.anh@keycove.vn',
                'first_name' => 'Minh Anh',
                'last_name'  => 'Tran',
                'shop_name'  => 'Minh Anh Digital',
            ],
            [
                'username'   => 'dungpc_keys',
                'email'      => 'dung.pham@keycove.vn',
                'first_name' => 'Dung',
                'last_name'  => 'Pham',
                'shop_name'  => 'Dung PC Keys',
            ],
            [
                'username'   => 'thuylinh_tech',
                'email'      => 'thuy.linh@keycove.vn',
                'first_name' => 'Thuy Linh',
                'last_name'  => 'Le',
                'shop_name'  => 'Thuy Linh Tech Store',
            ],
            [
                'username'   => 'hoanganh_hub',
                'email'      => 'hoang.anh@keycove.vn',
                'first_name' => 'Hoang Anh',
                'last_name'  => 'Vo',
                'shop_name'  => 'Hoang Anh Game Hub',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buyerData(): array
    {
        return [
            ['username' => 'namtran91', 'email' => 'nam.tran91@gmail.com', 'first_name' => 'Nam', 'last_name' => 'Tran'],
            ['username' => 'linhnguyen88', 'email' => 'linh.nguyen88@gmail.com', 'first_name' => 'Linh', 'last_name' => 'Nguyen'],
            ['username' => 'quangle.dev', 'email' => 'quang.le.dev@gmail.com', 'first_name' => 'Quang', 'last_name' => 'Le'],
            ['username' => 'hoangpham89', 'email' => 'hoang.pham89@gmail.com', 'first_name' => 'Hoang', 'last_name' => 'Pham'],
            ['username' => 'minhchau98', 'email' => 'minh.chau98@gmail.com', 'first_name' => 'Minh Chau', 'last_name' => 'Vu'],
            ['username' => 'anhthu_store', 'email' => 'anh.thu.store@gmail.com', 'first_name' => 'Anh Thu', 'last_name' => 'Ho'],
            ['username' => 'tungle91', 'email' => 'tung.le91@gmail.com', 'first_name' => 'Tung', 'last_name' => 'Le'],
            ['username' => 'thuylinh02', 'email' => 'thuy.linh02@gmail.com', 'first_name' => 'Thuy Linh', 'last_name' => 'Tran'],
            ['username' => 'ducanh07', 'email' => 'duc.anh07@gmail.com', 'first_name' => 'Duc Anh', 'last_name' => 'Nguyen'],
            ['username' => 'mytam99', 'email' => 'my.tam99@gmail.com', 'first_name' => 'My Tam', 'last_name' => 'Vo'],
            ['username' => 'trangngoc88', 'email' => 'trang.ngoc88@gmail.com', 'first_name' => 'Trang Ngoc', 'last_name' => 'Pham'],
            ['username' => 'khanhvu06', 'email' => 'khanh.vu06@gmail.com', 'first_name' => 'Khanh', 'last_name' => 'Vu'],
            ['username' => 'dungpham02', 'email' => 'dung.pham02@gmail.com', 'first_name' => 'Dung', 'last_name' => 'Pham'],
            ['username' => 'haidang98', 'email' => 'hai.dang98@gmail.com', 'first_name' => 'Hai Dang', 'last_name' => 'Do'],
            ['username' => 'vietanh.game', 'email' => 'viet.anh.game@gmail.com', 'first_name' => 'Viet Anh', 'last_name' => 'Nguyen'],
            ['username' => 'thanhha92', 'email' => 'thanh.ha92@gmail.com', 'first_name' => 'Thanh Ha', 'last_name' => 'Le'],
            ['username' => 'anhtuan03', 'email' => 'anh.tuan03@gmail.com', 'first_name' => 'Anh Tuan', 'last_name' => 'Tran'],
            ['username' => 'bichtram95', 'email' => 'bich.tram95@gmail.com', 'first_name' => 'Bich Tram', 'last_name' => 'Pham'],
            ['username' => 'phucminh21', 'email' => 'phuc.minh21@gmail.com', 'first_name' => 'Phuc Minh', 'last_name' => 'Bui'],
            ['username' => 'jennyphan', 'email' => 'jenny.phan@gmail.com', 'first_name' => 'Jenny', 'last_name' => 'Phan'],
        ];
    }

    protected function createSellers(): void
    {
        foreach ($this->sellerData() as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'password' => Hash::make('password'),
                    'role'     => UserRole::Seller,
                    'status'   => UserStatus::Active,
                ]
            );

            $this->ensureProfile($user, $data['first_name'], $data['last_name'], true);
            $this->ensureSeller($user, $data['shop_name'], $index);
            $this->ensureWallet($user);
            $this->ensureCart($user);
        }
    }

    protected function createBuyers(): void
    {
        foreach ($this->buyerData() as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'password' => Hash::make('password'),
                    'role'     => UserRole::User,
                    'status'   => UserStatus::Active,
                ]
            );

            $this->ensureProfile($user, $data['first_name'], $data['last_name'], false);
            $this->ensureCart($user);
        }
    }

    protected function ensureProfile(User $user, string $firstName, string $lastName, bool $isSeller): void
    {
        UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'gender'       => fake()->randomElement([Gender::Male, Gender::Female]),
                'phone_number' => '09'.fake()->numerify('########'),
                'avatar'       => 'avatars/'.($isSeller ? 'seller_' : 'user_').fake()->numberBetween(1, $isSeller ? 5 : 10).'.jpg',
                'bio'          => $isSeller
                    ? 'Shop bán key và license số uy tín, hỗ trợ nhanh trong giờ hành chính.'
                    : 'Người dùng thường xuyên mua game key, phần mềm và dịch vụ số trên KeyCove.',
            ]
        );
    }

    protected function ensureSeller(User $user, string $shopName, int $index): void
    {
        $frontImage = 'sellers/kyc/front_'.Str::uuid().'.jpg';
        $backImage = 'sellers/kyc/back_'.Str::uuid().'.jpg';

        $disk = config('filesystems.default');
        Storage::disk($disk)->makeDirectory('sellers/kyc');
        Storage::disk($disk)->put($frontImage, $this->downloadContent('https://placehold.co/800x500/EEE/31343C/png?text=Front+ID+Card'));
        Storage::disk($disk)->put($backImage, $this->downloadContent('https://placehold.co/800x500/EEE/31343C/png?text=Back+ID+Card'));

        Seller::firstOrCreate(
            ['user_id' => $user->id],
            [
                'shop_name'        => $shopName,
                'cccd_number'      => fake()->numerify('############'),
                'cccd_front_image' => $frontImage,
                'cccd_back_image'  => $backImage,
                'kyc_status'       => KycStatus::Approved,
                'created_at'       => now()->subDays(45 - ($index * 5)),
                'updated_at'       => now()->subDays(20 - ($index * 2)),
            ]
        );
    }

    protected function ensureWallet(User $user): void
    {
        $seller = $user->seller()->first();

        if (! $seller) {
            return;
        }

        Wallet::firstOrCreate(
            ['seller_id' => $seller->id],
            [
                'balance' => fake()->numberBetween(8_000_000, 85_000_000),
                'holding' => fake()->numberBetween(500_000, 8_000_000),
            ]
        );
    }

    protected function ensureCart(User $user): void
    {
        Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);
    }

    protected function downloadContent(string $url): string
    {
        $response = Http::timeout(10)->get($url);

        if ($response->successful()) {
            return $response->body();
        }

        return '';
    }
}
