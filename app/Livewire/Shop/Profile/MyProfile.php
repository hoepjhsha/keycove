<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Profile;

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

        return view('pages.shop.profile.my-profile', [
            'user'              => $user,
            'profile'           => $profile,
            'avatarUrl'         => $avatarUrl,
            'fullName'          => $fullName !== '' ? $fullName : $user->username,
            'profileCompletion' => $profileCompletion,
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
        if (! in_array($this->section, ['profile', 'security'], true)) {
            $this->section = 'profile';
        }
    }
}
