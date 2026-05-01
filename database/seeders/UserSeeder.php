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
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private string $defaultPassword;

    private string $frontKycImageContent = '';

    private string $backKycImageContent = '';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->defaultPassword = Hash::make('password');

        $this->prepareKycImages();

        $this->createSellers();
        $this->createBuyers(50);
    }

    protected function prepareKycImages(): void
    {
        $disk = config('filesystems.default', 'public');
        Storage::disk($disk)->makeDirectory('sellers/kyc');

        $this->frontKycImageContent = $this->downloadContent('https://placehold.co/800x500/EEE/31343C/png?text=Front+ID+Card') ?: 'fallback';
        $this->backKycImageContent = $this->downloadContent('https://placehold.co/800x500/EEE/31343C/png?text=Back+ID+Card') ?: 'fallback';
    }

    protected function sellerData(): array
    {
        return [
            ['username' => 'divineshop', 'email' => 'contact@divineshop.vn', 'first_name' => 'Divine', 'last_name' => 'Shop', 'shop_name' => 'Divine Shop Official'],
            ['username' => 'wongstore', 'email' => 'sp@wongstore.com', 'first_name' => 'Wong', 'last_name' => 'Store', 'shop_name' => 'Wong Store Keys'],
            ['username' => 'khanggame', 'email' => 'khang.nguyen@keycove.vn', 'first_name' => 'Khang', 'last_name' => 'Nguyen', 'shop_name' => 'Khang Game Key'],
            ['username' => 'bachhoagame', 'email' => 'admin@bachhoagame.vn', 'first_name' => 'Bách Hóa', 'last_name' => 'Game', 'shop_name' => 'Bách Hóa Game'],
            ['username' => 'gearvn_soft', 'email' => 'software@gearvn.com', 'first_name' => 'GearVN', 'last_name' => 'Software', 'shop_name' => 'GearVN Digital'],
            ['username' => 'keygiare_vn', 'email' => 'sale@keygiare.vn', 'first_name' => 'Key', 'last_name' => 'Giá Rẻ', 'shop_name' => 'Key Giá Rẻ VN'],
            ['username' => 'haidang_pc', 'email' => 'haidang.pc@gmail.com', 'first_name' => 'Hải Đăng', 'last_name' => 'PC', 'shop_name' => 'Hải Đăng PC & Keys'],
            ['username' => 'xomgame', 'email' => 'admin@xomgame.vn', 'first_name' => 'Xóm', 'last_name' => 'Game', 'shop_name' => 'Xóm Game Thể Loại'],
            ['username' => 'thegioikey', 'email' => 'thegioikey@yahoo.com', 'first_name' => 'Thế Giới', 'last_name' => 'Key', 'shop_name' => 'Thế Giới Key Windows'],
            ['username' => 'steam_wallet_vn', 'email' => 'steamvn@gmail.com', 'first_name' => 'Steam', 'last_name' => 'VN', 'shop_name' => 'Tổng Kho Steam Wallet'],
            ['username' => 'netflix_giare', 'email' => 'netflix.share@gmail.com', 'first_name' => 'Tài Khoản', 'last_name' => 'Giải Trí', 'shop_name' => 'Trạm Giải Trí Số'],
            ['username' => 'minhanh_store', 'email' => 'minh.anh@keycove.vn', 'first_name' => 'Minh Anh', 'last_name' => 'Tran', 'shop_name' => 'Minh Anh Digital'],
            ['username' => 'dungpc_keys', 'email' => 'dung.pham@keycove.vn', 'first_name' => 'Dung', 'last_name' => 'Pham', 'shop_name' => 'Dung PC Keys'],
            ['username' => 'thuylinh_tech', 'email' => 'thuy.linh@keycove.vn', 'first_name' => 'Thuy Linh', 'last_name' => 'Le', 'shop_name' => 'Thuy Linh Tech Store'],
            ['username' => 'hoanganh_hub', 'email' => 'hoang.anh@keycove.vn', 'first_name' => 'Hoang Anh', 'last_name' => 'Vo', 'shop_name' => 'Hoang Anh Game Hub'],
        ];
    }

    protected function createSellers(): void
    {
        foreach ($this->sellerData() as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'username'          => $data['username'],
                    'password'          => $this->defaultPassword,
                    'email_verified_at' => now(),
                    'role'              => UserRole::Seller,
                    'status'            => UserStatus::Active,
                ]
            );

            $this->ensureProfile($user, $data['first_name'], $data['last_name'], true);
            $this->ensureSeller($user, $data['shop_name'], $index);
            $this->ensureWallet($user);
            $this->ensureCart($user);
        }
    }

    protected function createBuyers(int $count): void
    {
        $fixedBuyers = [
            ['username' => 'namtran91', 'email' => 'nam.tran91@gmail.com', 'first_name' => 'Nam', 'last_name' => 'Tran'],
            ['username' => 'linhnguyen88', 'email' => 'linh.nguyen88@gmail.com', 'first_name' => 'Linh', 'last_name' => 'Nguyen'],
            ['username' => 'quangle.dev', 'email' => 'quang.le.dev@gmail.com', 'first_name' => 'Quang', 'last_name' => 'Le'],
            ['username' => 'buyer1', 'email' => 'buyer1@keycove.vn', 'first_name' => 'Test', 'last_name' => 'Buyer 1'],
            ['username' => 'buyer2', 'email' => 'buyer2@keycove.vn', 'first_name' => 'Test', 'last_name' => 'Buyer 2'],
        ];

        foreach ($fixedBuyers as $data) {
            $this->makeBuyer($data['email'], $data['username'], $data['first_name'], $data['last_name']);
        }

        for ($i = 0; $i < $count - count($fixedBuyers); $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $username = Str::slug($firstName.$lastName).fake()->numberBetween(10, 9999);
            $email = $username.'@gmail.com';

            $this->makeBuyer($email, $username, $firstName, $lastName);
        }
    }

    protected function makeBuyer(string $email, string $username, string $firstName, string $lastName): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'username'          => $username,
                'password'          => $this->defaultPassword,
                'email_verified_at' => now(),
                'role'              => UserRole::User,
                'status'            => UserStatus::Active,
            ]
        );

        $this->ensureProfile($user, $firstName, $lastName, false);
        $this->ensureCart($user);
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
                'avatar'       => 'https://ui-avatars.com/api/?name='.urlencode($firstName.'+'.$lastName).'&background=random&color=fff',
                'bio'          => $isSeller
                    ? 'Shop bán key và license số uy tín, bảo hành trọn đời, hỗ trợ nhanh trong giờ hành chính.'
                    : 'Người dùng thường xuyên mua game key, phần mềm và dịch vụ số trên KeyCove.',
            ]
        );
    }

    protected function ensureSeller(User $user, string $shopName, int $index): void
    {
        $disk = config('filesystems.default', 'public');
        $frontImage = 'sellers/kyc/front_'.Str::uuid().'.jpg';
        $backImage = 'sellers/kyc/back_'.Str::uuid().'.jpg';

        Storage::disk($disk)->put($frontImage, $this->frontKycImageContent);
        Storage::disk($disk)->put($backImage, $this->backKycImageContent);

        Seller::firstOrCreate(
            ['user_id' => $user->id],
            [
                'shop_name'        => $shopName,
                'cccd_number'      => fake()->numerify('0010########'),
                'cccd_front_image' => $frontImage,
                'cccd_back_image'  => $backImage,
                'kyc_status'       => KycStatus::Approved,
                'created_at'       => now()->subDays(45 - ($index * 2)),
                'updated_at'       => now()->subDays(20 - $index),
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
                'balance' => 0,
                'holding' => 0,
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
        try {
            $response = Http::timeout(5)->get($url);
            if ($response->successful()) {
                return $response->body();
            }
        } catch (Exception $e) {
        }

        return '';
    }
}
