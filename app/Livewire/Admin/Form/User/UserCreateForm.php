<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\User;

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Cart;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserCreateForm extends Form
{
    #[Validate([
        'required',
        'string',
        'max:255',
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
    ])]
    public string $password = '';

    #[Validate([
        'required',
        'integer',
        new Enum(UserRole::class),
    ])]
    public int $role;

    #[Validate([
        'required',
        'integer',
        new Enum(UserStatus::class),
    ])]
    public int $status;

    public function store(): User|false
    {
        $this->validate();

        $currentUserRole = auth('admin')->user()->role;
        $targetRole = UserRole::from($this->role);

        if ($targetRole === UserRole::SuperAdmin) {
            throw ValidationException::withMessages([
                'createForm.role' => __('admin.validation.user_no_permission_create_super_admin'),
            ]);
        }

        if ($currentUserRole !== UserRole::SuperAdmin && $targetRole === UserRole::Admin) {
            throw ValidationException::withMessages([
                'createForm.role' => __('admin.validation.user_no_permission_assign_role'),
            ]);
        }

        $user = User::create([
            'username'          => $this->username,
            'email'             => $this->email,
            'password'          => Hash::make($this->password),
            'role'              => $targetRole,
            'status'            => UserStatus::from($this->status),
            'email_verified_at' => now(), // Assume admin created users are verified
        ]);

        if ($user) {
            UserProfile::create([
                'user_id' => $user->id,
            ]);

            Cart::firstOrCreate([
                'user_id' => $user->id,
            ]);

            if ($targetRole === UserRole::Seller) {
                $seller = Seller::create([
                    'user_id'          => $user->id,
                    'shop_name'        => $user->username.' Store',
                    'cccd_number'      => fake()->numerify('############'),
                    'cccd_front_image' => null,
                    'cccd_back_image'  => null,
                    'kyc_status'       => KycStatus::Pending,
                ]);

                Wallet::create([
                    'seller_id' => $seller->id,
                    'balance'   => 0,
                    'holding'   => 0,
                ]);
            }
        }

        return $user;
    }
}
