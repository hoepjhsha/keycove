<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
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
                'editForm.username' => 'The username has already been taken.',
            ]);
        }

        if (User::where('email', $this->email)->where('id', '!=', $this->user->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.email' => 'The email has already been taken.',
            ]);
        }

        $currentUserRole = auth('admin')->user()->role;
        $targetRole = UserRole::from($this->role);

        if ($this->status === UserStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => 'Cannot set status to Deleted via update.',
            ]);
        }

        if ($targetRole === UserRole::SuperAdmin) {
            throw ValidationException::withMessages([
                'editForm.role' => 'Cannot assign Super Admin role.',
            ]);
        }

        if ($this->user->role === UserRole::SuperAdmin) {
            throw ValidationException::withMessages([
                'editForm.role' => 'Super Admin cannot be edited.',
            ]);
        }

        if ($this->user->role === UserRole::Admin && $targetRole !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'editForm.role' => 'Cannot change the role of an Admin user.',
            ]);
        }

        if ($targetRole === UserRole::Admin && $this->user->role !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'editForm.role' => 'Cannot promote an existing user to Admin.',
            ]);
        }

        if ($currentUserRole !== UserRole::SuperAdmin && $targetRole === UserRole::Admin) {
            throw ValidationException::withMessages([
                'editForm.role' => 'You do not have permission to assign this role.',
            ]);
        }

        // Prevent admin from editing existing admin or super admin
        if ($currentUserRole !== UserRole::SuperAdmin && in_array($this->user->role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
            throw ValidationException::withMessages([
                'editForm.role' => 'You do not have permission to edit this user.',
            ]);
        }

        $data = [
            'username' => $this->username,
            'email' => $this->email,
            'role' => $targetRole,
            'status' => UserStatus::from($this->status),
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        return $this->user->update($data);
    }
}
