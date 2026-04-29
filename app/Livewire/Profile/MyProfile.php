<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\User;
use App\Utilities\StorageUtility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Profile')]
class MyProfile extends Component
{
    public function render(): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

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

        return view('pages.profile.my-profile', [
            'user'              => $user,
            'profile'           => $profile,
            'avatarUrl'         => $avatarUrl,
            'fullName'          => $fullName !== '' ? $fullName : $user->username,
            'profileCompletion' => $profileCompletion,
        ])->layout('components.layouts.shop');
    }
}
