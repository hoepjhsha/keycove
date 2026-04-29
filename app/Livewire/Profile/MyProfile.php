<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\User;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('My Profile')]
class MyProfile extends Component
{
    #[Url(except: 'profile')]
    public string $section = 'profile';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public string $keyAccessPassword = '';

    public ?int $keyAccessOrderItemId = null;

    /**
     * @var array<int, list<string>>
     */
    public array $revealedKeys = [];

    public function mount(): void
    {
        $this->normalizeSection();
    }

    public function setSection(string $section): void
    {
        $this->section = $section;
        $this->normalizeSection();
    }

    public function sendVerificationLink(): void
    {
        $user = $this->resolveUser();

        if ($user->hasVerifiedEmail()) {
            session()->flash('profile-status', 'Your email is already verified.');

            return;
        }

        $user->sendEmailVerificationNotification();

        session()->flash('profile-status', 'A verification link has been sent to your email address.');
        $this->section = 'security';
    }

    public function changePassword(): void
    {
        $user = $this->resolveUser();

        $validated = $this->validate([
            'currentPassword'         => ['required', 'current_password'],
            'newPassword'             => ['required', 'string', Password::min(8)],
            'newPasswordConfirmation' => ['required', 'same:newPassword'],
        ], [
            'newPasswordConfirmation.same' => 'The new password confirmation does not match.',
        ]);

        if (Hash::check($validated['newPassword'], $user->password)) {
            $this->addError('newPassword', 'Please choose a different password from your current one.');

            return;
        }

        $user->update([
            'password' => $validated['newPassword'],
        ]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        session()->flash('profile-status', 'Your password has been updated successfully.');
        $this->section = 'security';
    }

    public function promptKeyReveal(int $orderItemId): void
    {
        $orderItem = $this->resolveOwnedOrderItem($orderItemId);

        if (! $this->canRevealKeys($orderItem)) {
            return;
        }

        $keys = $this->orderItemKeys($orderItem);

        if ($keys === []) {
            return;
        }

        if ($orderItem->buyer_key_viewed_at !== null) {
            $this->revealedKeys[$orderItem->id] = $keys;
            $this->keyAccessOrderItemId = null;
            $this->keyAccessPassword = '';
            $this->section = 'orders';

            return;
        }

        $this->resetValidation('keyAccessPassword');
        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = $orderItem->id;
        $this->section = 'orders';
    }

    public function cancelKeyReveal(): void
    {
        $this->resetValidation('keyAccessPassword');
        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = null;
    }

    public function revealOrderItemKeys(): void
    {
        $this->validate([
            'keyAccessPassword' => ['required', 'current_password'],
        ]);

        $orderItem = $this->resolveOwnedOrderItem((int) $this->keyAccessOrderItemId);

        if (! $this->canRevealKeys($orderItem)) {
            return;
        }

        $keys = $this->orderItemKeys($orderItem);

        if ($keys === []) {
            $this->addError('keyAccessPassword', 'No product keys are attached to this order item yet.');

            return;
        }

        $this->revealedKeys[$orderItem->id] = $keys;

        if ($orderItem->buyer_key_viewed_at === null) {
            $orderItem->forceFill([
                'buyer_key_viewed_at' => now(),
            ])->save();
        }

        $this->keyAccessPassword = '';
        $this->keyAccessOrderItemId = null;
        session()->flash('profile-status', 'Your key is now visible below. Stay on this screen while reviewing and activating it.');
    }

    public function hideOrderItemKeys(int $orderItemId): void
    {
        unset($this->revealedKeys[$orderItemId]);
    }

    public function render(): View
    {
        $user = $this->resolveUser();

        $user->loadMissing('profile');

        $profile = $user->profile;
        $avatarUrl = null;

        if (filled($profile?->avatar)) {
            $avatarUrl = Str::startsWith($profile->avatar, ['http://', 'https://'])
                ? $profile->avatar
                : StorageUtility::getUrl($profile->avatar);
        }

        $fullName = trim(collect([
            $profile?->first_name,
            $profile?->last_name,
        ])->filter()->implode(' '));

        $profileCompletion = (int) round(
            collect([
                filled($profile?->first_name),
                filled($profile?->last_name),
                filled($profile?->avatar),
                filled($profile?->dob),
                $profile?->gender?->value !== 0,
                filled($profile?->phone_number),
                filled($profile?->bio),
            ])->filter()->count() / 7 * 100
        );

        $orders = $user->orders()
            ->with([
                'items' => fn ($query) => $query
                    ->select(['id', 'order_id', 'listing_id', 'product_name_snapshot', 'quantity', 'unit_price', 'subtotal', 'status', 'buyer_key_viewed_at'])
                    ->with(['listing.variant.product'])
                    ->withCount('keys')
                    ->orderBy('id'),
            ])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('pages.shop.profile.my-profile', [
            'user'              => $user,
            'profile'           => $profile,
            'avatarUrl'         => $avatarUrl,
            'fullName'          => $fullName !== '' ? $fullName : $user->username,
            'profileCompletion' => $profileCompletion,
            'orders'            => $orders,
        ])->layout('components.layouts.shop');
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function normalizeSection(): void
    {
        if (! in_array($this->section, ['profile', 'orders', 'security'], true)) {
            $this->section = 'profile';
        }
    }

    protected function resolveOwnedOrderItem(int $orderItemId): OrderItem
    {
        return OrderItem::query()
            ->whereKey($orderItemId)
            ->whereHas('order', function ($query): void {
                $query->where('buyer_id', $this->resolveUser()->id);
            })
            ->firstOrFail();
    }

    protected function canRevealKeys(OrderItem $orderItem): bool
    {
        return in_array($orderItem->status, [
            OrderStatus::Delivered,
            OrderStatus::Disputing,
            OrderStatus::Completed,
        ], true);
    }

    /**
     * @return list<string>
     */
    protected function orderItemKeys(OrderItem $orderItem): array
    {
        return $orderItem->keys()
            ->orderBy('id')
            ->get(['id', 'order_item_id', 'key_code'])
            ->pluck('key_code')
            ->filter(fn (?string $keyCode): bool => filled($keyCode))
            ->values()
            ->all();
    }
}
