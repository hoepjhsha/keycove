<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Form;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ResetPasswordForm extends Form
{
    #[Validate([
        'required',
        'string',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
    ])]
    public string $password = '';

    #[Validate([
        'required',
        'string',
        'same:password',
    ])]
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        $this->validate();

        $email = session('reset_password_email');
        $user = User::where('email', $email)->firstOrFail();

        $user->update([
            'password' => Hash::make($this->password),
        ]);
    }
}
