<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserBulkChangeStatusForm extends Form
{
    #[Validate([
        'required',
        'int',
        new Enum(UserStatus::class),
    ])]
    public int $status;

    public function setStatus(array $ids): bool
    {
        $this->validate();

        $currentUserRole = auth('admin')->user()->role;
        $query = User::whereIn('id', $ids);

        // Prevent modification of Admins/SuperAdmins by regular Admins
        if ($currentUserRole !== UserRole::SuperAdmin) {
            $query->whereNotIn('role', [UserRole::SuperAdmin, UserRole::Admin]);
        } else {
            // SuperAdmins cannot modify other SuperAdmins
            $query->where('role', '!=', UserRole::SuperAdmin);
        }

        // Users might have soft deletes but let's assume standard behavior
        return $query->update([
            'status' => $this->status,
        ]) > 0;
    }
}
