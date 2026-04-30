<?php

use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('complaints.{complaintId}', function (User $user, int $complaintId): bool {
    if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true)) {
        return true;
    }

    $complaint = Complaint::query()
        ->with(['orderItem.order.buyer', 'orderItem.seller.user'])
        ->find($complaintId);

    if (! $complaint) {
        return false;
    }

    return in_array($user->id, [
        $complaint->orderItem?->order?->buyer?->id,
        $complaint->orderItem?->seller?->user?->id,
    ], true);
}, ['guards' => ['web', 'admin']]);
