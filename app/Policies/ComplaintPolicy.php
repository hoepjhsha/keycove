<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        $buyer = $complaint->orderItem?->order?->buyer_id === $user->id;

        if ($buyer) {
            return true;
        }

        return $complaint->orderItem?->seller?->user_id === $user->id;
    }

    public function reply(User $user, Complaint $complaint): bool
    {
        if (! $this->view($user, $complaint)) {
            return false;
        }

        return ! in_array($complaint->status, [ComplaintStatus::ApprovedRefund, ComplaintStatus::RejectedRelease], true);
    }

    public function resolve(User $user, Complaint $complaint): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return false;
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return false;
    }

    public function restore(User $user, Complaint $complaint): bool
    {
        return false;
    }

    public function forceDelete(User $user, Complaint $complaint): bool
    {
        return false;
    }

    private function isAdmin(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true);
    }
}
