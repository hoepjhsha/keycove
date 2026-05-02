<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\User;

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Cart;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserEditForm extends Form
{
    public ?User $user = null;

    #[Validate([
        'required',
        'string',
        'max:255',
    ])]
    public string $username = '';

    #[Validate([
        'required',
        'string',
        'email',
        'max:255',
    ])]
    public string $email = '';

    #[Validate([
        'nullable',
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

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->status = $user->status->value;
        $this->password = ''; // Don't pre-fill password
    }

    public function update(): bool
    {
        $this->validate();

        if (User::where('username', $this->username)->where('id', '!=', $this->user->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.username' => __('admin.validation.duplicate_username'),
            ]);
        }

        if (User::where('email', $this->email)->where('id', '!=', $this->user->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.email' => __('admin.validation.duplicate_user_email'),
            ]);
        }

        $currentUserRole = auth('admin')->user()->role;
        $targetRole = UserRole::from($this->role);

        if ($this->status === UserStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => __('admin.validation.status_deleted_update'),
            ]);
        }

        if ($targetRole === UserRole::SuperAdmin) {
            throw ValidationException::withMessages([
                'editForm.role' => __('admin.validation.user_cannot_assign_super_admin'),
            ]);
        }

        if ($this->user->role === UserRole::SuperAdmin) {
            throw ValidationException::withMessages([
                'editForm.role' => __('admin.validation.user_cannot_edit_super_admin'),
            ]);
        }

        if ($this->user->role === UserRole::Admin && $targetRole !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'editForm.role' => __('admin.validation.user_cannot_change_admin_role'),
            ]);
        }

        if ($targetRole === UserRole::Admin && $this->user->role !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'editForm.role' => __('admin.validation.user_cannot_assign_admin'),
            ]);
        }

        if ($currentUserRole !== UserRole::SuperAdmin && $targetRole === UserRole::Admin) {
            throw ValidationException::withMessages([
                'editForm.role' => __('admin.validation.user_no_permission_assign_role'),
            ]);
        }

        // Prevent admin from editing existing admin or super admin
        if ($currentUserRole !== UserRole::SuperAdmin && in_array($this->user->role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
            throw ValidationException::withMessages([
                'editForm.role' => __('admin.validation.user_no_permission_edit'),
            ]);
        }

        $data = [
            'username' => $this->username,
            'email'    => $this->email,
            'role'     => $targetRole,
            'status'   => UserStatus::from($this->status),
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $updated = $this->user->update($data);

        if ($updated) {
            Cart::firstOrCreate([
                'user_id' => $this->user->id,
            ]);

            if ($targetRole === UserRole::Seller && ! $this->user->seller) {
                $seller = Seller::create([
                    'user_id'          => $this->user->id,
                    'shop_name'        => $this->user->username.' Store',
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

        return $updated;
    }
}
