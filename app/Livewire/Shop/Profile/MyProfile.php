<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Profile;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Models\User;
use App\Models\UserProfile;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('My Profile')]
class MyProfile extends Component
{
    use WithFileUploads;

    #[Url(except: 'profile')]
    public string $section = 'profile';

    public string $username = '';

    public string $firstName = '';

    public string $lastName = '';

    public $avatar = null;

    public string $dob = '';

    public int $gender = 0;

    public string $phoneNumber = '';

    public string $bio = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public function mount(): void
    {
        $this->normalizeSection();
        $this->syncProfileForm();
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

    public function saveProfile(): void
    {
        $user = $this->resolveUser();

        $validated = $this->validate([
            'username'    => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'firstName'   => ['required', 'string', 'max:50'],
            'lastName'    => ['required', 'string', 'max:50'],
            'avatar'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:12288'],
            'dob'         => ['nullable', 'date'],
            'gender'      => ['required', 'integer', new Enum(Gender::class)],
            'phoneNumber' => ['nullable', 'string', 'max:15'],
            'bio'         => ['nullable', 'string', 'max:1000'],
        ]);

        $profile = $user->load('profile')->profile;
        $currentAvatar = $profile?->avatar;
        $avatarPath = $currentAvatar;

        if ($this->avatar !== null) {
            $avatarPath = StorageUtility::store($this->avatar, 'avatars', config('filesystems.public_disk'));
        }

        DB::transaction(function () use ($user, $avatarPath, $currentAvatar): void {
            $user->update([
                'username' => $this->username,
            ]);

            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name'   => $this->firstName,
                    'last_name'    => $this->lastName,
                    'avatar'       => $avatarPath,
                    'dob'          => filled($this->dob) ? $this->dob : null,
                    'gender'       => Gender::from($this->gender),
                    'phone_number' => filled($this->phoneNumber) ? $this->phoneNumber : null,
                    'bio'          => filled($this->bio) ? $this->bio : null,
                ]
            );

            if ($this->avatar !== null && filled($profile->avatar) && $currentAvatar && $currentAvatar !== $profile->avatar && ! Str::startsWith($currentAvatar, ['http://', 'https://'])) {
                StorageUtility::delete($currentAvatar, config('filesystems.public_disk'));
            }
        });

        $user->load('profile');
        $this->syncProfileForm($user->profile);

        session()->flash('profile-status', 'Your profile has been updated successfully.');
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

        $user->loadMissing(['profile', 'seller']);

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
            'user'                     => $user,
            'profile'                  => $profile,
            'avatarUrl'                => $avatarUrl,
            'fullName'                 => $fullName !== '' ? $fullName : $user->username,
            'profileCompletion'        => $profileCompletion,
            'hasApprovedSellerAccount' => $user->seller?->kyc_status === KycStatus::Approved,
            'genderOptions'            => Gender::cases(),
        ])->layout('components.layouts.shop');
    }

    protected function syncProfileForm(?UserProfile $profile = null): void
    {
        $user = $this->resolveUser();
        $profile ??= $user->profile;

        $this->username = $user->username;
        $this->firstName = $profile?->first_name ?? '';
        $this->lastName = $profile?->last_name ?? '';
        $this->avatar = null;
        $this->dob = $profile?->dob?->format('Y-m-d') ?? '';
        $this->gender = $profile?->gender?->value ?? Gender::Unknown->value;
        $this->phoneNumber = $profile?->phone_number ?? '';
        $this->bio = $profile?->bio ?? '';
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
