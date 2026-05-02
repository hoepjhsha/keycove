<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\KycStatus;
use App\Enums\WalletType;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Trở thành người bán')]
class Apply extends Component
{
    use WithFileUploads;

    public string $shopName = '';

    public string $cccdNumber = '';

    public mixed $cccdFrontImage = null;

    public mixed $cccdBackImage = null;

    public ?Seller $seller = null;

    public function mount(): void
    {
        $user = $this->resolveUser();
        $user->load('seller');

        $this->seller = $user->seller;
        $this->syncForm();
    }

    public function submit(): void
    {
        $user = $this->resolveUser();

        if ($user->seller?->kyc_status === KycStatus::Approved) {
            session()->flash('seller-status', 'Tài khoản người bán của bạn đã được phê duyệt.');

            return;
        }

        $this->validate([
            'shopName'       => ['required', 'string', 'max:255'],
            'cccdNumber'     => ['required', 'digits:12'],
            'cccdFrontImage' => $this->seller?->cccd_front_image
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288']
                : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'cccdBackImage' => $this->seller?->cccd_back_image
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288']
                : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);

        $previousFrontImage = $this->seller?->cccd_front_image;
        $previousBackImage = $this->seller?->cccd_back_image;

        $frontImagePath = $this->storeImage($this->cccdFrontImage) ?? $previousFrontImage;
        $backImagePath = $this->storeImage($this->cccdBackImage) ?? $previousBackImage;

        DB::transaction(function () use ($user, $frontImagePath, $backImagePath): void {
            $seller = Seller::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name'           => $this->shopName,
                    'cccd_number'         => $this->cccdNumber,
                    'cccd_front_image'    => $frontImagePath,
                    'cccd_back_image'     => $backImagePath,
                    'kyc_status'          => KycStatus::Pending,
                    'kyc_rejected_reason' => null,
                ]
            );

            Wallet::firstOrCreate(
                ['seller_id' => $seller->id],
                [
                    'type'    => WalletType::Seller,
                    'code'    => Str::upper(Str::random(12)),
                    'balance' => 0,
                    'holding' => 0,
                ]
            );
        });

        if ($this->cccdFrontImage !== null && filled($previousFrontImage) && $frontImagePath !== $previousFrontImage) {
            StorageUtility::delete($previousFrontImage, config('filesystems.default'));
        }

        if ($this->cccdBackImage !== null && filled($previousBackImage) && $backImagePath !== $previousBackImage) {
            StorageUtility::delete($previousBackImage, config('filesystems.default'));
        }

        $user->load('seller');
        $this->seller = $user->seller;
        $this->cccdFrontImage = null;
        $this->cccdBackImage = null;
        $this->syncForm();

        session()->flash('seller-status', 'Hồ sơ người bán của bạn đã được gửi để xét duyệt.');
    }

    public function render(): View
    {
        $user = $this->resolveUser();

        $portalState = match ($this->seller?->kyc_status) {
            KycStatus::Approved => [
                'badge'       => 'Đã duyệt',
                'badgeClass'  => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                'headline'    => 'Tài khoản người bán đã được duyệt',
                'description' => 'Hồ sơ của bạn đã hoàn tất. Bạn có thể mở bảng điều khiển người bán ngay.',
                'actionLabel' => 'Mở bảng điều khiển người bán',
                'actionUrl'   => route('seller.dashboard.index'),
                'actionClass' => 'bg-black text-white hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white',
            ],
            KycStatus::Pending => [
                'badge'       => 'Đang chờ',
                'badgeClass'  => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                'headline'    => 'Hồ sơ đang chờ xét duyệt',
                'description' => 'Hồ sơ người bán của bạn đang chờ quản trị viên phê duyệt.',
                'actionLabel' => 'Quay lại hồ sơ',
                'actionUrl'   => url('/my-profile'),
                'actionClass' => 'border border-slate-200 bg-white text-slate-700 hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-slate-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]',
            ],
            KycStatus::Rejected => [
                'badge'       => 'Bị từ chối',
                'badgeClass'  => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                'headline'    => 'Hồ sơ cần chỉnh sửa',
                'description' => 'Cập nhật thông tin bên dưới và gửi lại hồ sơ.',
                'actionLabel' => 'Gửi lại hồ sơ',
                'actionUrl'   => null,
                'actionClass' => 'bg-black text-white hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white',
            ],
            default => [
                'badge'       => 'Sẵn sàng',
                'badgeClass'  => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
                'headline'    => 'Trở thành người bán',
                'description' => 'Gửi tên cửa hàng và thông tin KYC để mở cổng người bán.',
                'actionLabel' => 'Gửi hồ sơ',
                'actionUrl'   => null,
                'actionClass' => 'bg-black text-white hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white',
            ],
        };

        return view('pages.shop.seller.apply', [
            'user'        => $user,
            'seller'      => $this->seller,
            'portalState' => $portalState,
        ])->layout('components.layouts.seller', [
            'title'         => 'Hồ sơ người bán',
            'user'          => $user,
            'seller'        => $this->seller,
            'activeSection' => 'application',
        ]);
    }

    protected function syncForm(): void
    {
        $this->shopName = $this->seller?->shop_name ?? '';
        $this->cccdNumber = $this->seller?->cccd_number ?? '';
    }

    protected function storeImage(mixed $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return StorageUtility::store($file, 'sellers/kyc');
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
