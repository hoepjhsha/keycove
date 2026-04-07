<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Form;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RegisterForm extends Form
{
    #[Validate([
        'required',
        'string',
        'min:3',
        'max:20',
        'regex:/^[a-z][a-z0-9]*$/',
        new Unique(table: User::class, column: 'username'),
    ])]
    public string $username = '';

    #[Validate([
        'required',
        'string',
        'email',
        'max:255',
        new Unique(table: User::class, column: 'email'),
    ])]
    public string $email = '';

    #[Validate([
        'required',
        'string',
        'min:8',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
    ])]
    public string $password = '';

    #[Validate([
        'required',
        'same:password',
    ])]
    public string $passwordConfirmation = '';

    #[Validate('accepted', message: 'You must accept the terms and conditions.')]
    public bool $terms = false;

    public function storeUser(): User
    {
        $this->validate();

        return User::create([
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);
    }
}
