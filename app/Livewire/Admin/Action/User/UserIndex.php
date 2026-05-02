<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Livewire\Admin\Form\User\UserBulkChangeStatusForm;
use App\Livewire\Admin\Form\User\UserCreateForm;
use App\Livewire\Admin\Form\User\UserEditForm;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage Users')]
class UserIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public bool $showBulkStatusModal = false;

    public ?array $viewData = null;

    public array $bulkSelectedIds = [];

    public UserCreateForm $createForm;

    public UserEditForm $editForm;

    public UserBulkChangeStatusForm $bulkChangeStatusForm;

    public function createUser(): void
    {
        $result = $this->createForm->store();
        if ($result) {
            $this->reset('createForm');
            $this->dispatch('swal:success', ['message' => __('admin.messages.created', ['Name' => __('admin.common.user')])]);
        }

        $this->showCreateModal = false;
    }

    public function updateUser(): void
    {
        $result = $this->editForm->update();
        if ($result) {
            $this->dispatch('swal:success', ['message' => __('admin.messages.updated', ['Name' => __('admin.common.user')])]);
        }
        $this->showEditModal = false;
    }

    public function bulkChangeStatusUser(): void
    {
        if (User::withTrashed()
            ->whereIn('id', $this->bulkSelectedIds)
            ->where('status', UserStatus::Deleted)
            ->exists()
        ) {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.cannot_change_deleted_status'));
            $this->showBulkStatusModal = false;

            return;
        }

        $result = $this->bulkChangeStatusForm->setStatus($this->bulkSelectedIds);

        if ($result) {
            $this->dispatch('swal:success', ['message' => __('admin.messages.status_updated_selected')]);

            $this->bulkChangeStatusForm->reset();
            $this->bulkSelectedIds = [];
            $this->dispatch('pg:eventRefresh-userTable');
        } else {
            sweetalert()->title(__('admin.common.error'))->showConfirmButton(false)->error(__('admin.messages.failed_update_status'));
        }

        $this->showBulkStatusModal = false;
    }

    public function render()
    {
        return view('pages.admin.user.index')->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewUser')]
    public function viewUser($rowId): void
    {
        $user = User::find($rowId);

        if ($user) {
            $colorClass = match ($user->status) {
                UserStatus::Active   => 'bg-green-500/10 text-green-500',
                UserStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                UserStatus::Blocked  => 'bg-yellow-500/10 text-yellow-500',
                UserStatus::Deleted  => 'bg-red-500/10 text-red-500',
                default              => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$user->status->label().'</span>';

            $roleClass = match ($user->role) {
                UserRole::User       => 'bg-blue-500/10 text-blue-500',
                UserRole::Seller     => 'bg-indigo-500/10 text-indigo-500',
                UserRole::Admin      => 'bg-purple-500/10 text-purple-500',
                UserRole::SuperAdmin => 'bg-emerald-500/10 text-emerald-500',
            };
            $roleLabel = '<span class="'.$roleClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$user->role->label().'</span>';

            $this->viewData = [
                'id'           => $user->id,
                'username'     => $user->username,
                'email'        => $user->email,
                'role_label'   => $roleLabel,
                'status_label' => $statusLabel,
                'created_at'   => $user->created_at->format('d/m/Y H:i:s'),
                'updated_at'   => $user->updated_at->format('d/m/Y H:i:s'),
                'deleted_at'   => $user->deleted_at?->format('d/m/Y H:i:s'),
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editUser')]
    public function editUser($rowId): void
    {
        $user = User::find($rowId);
        $currentUserRole = auth('admin')->user()->role;

        if ($user && $user->role === UserRole::SuperAdmin) {
            $this->dispatch('swal:error', ['message' => __('admin.validation.user_cannot_edit_super_admin')]);

            return;
        }

        // Prevent editing Admin or SuperAdmin as Admin
        if ($currentUserRole !== UserRole::SuperAdmin && in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
            $this->dispatch('swal:error', ['message' => __('admin.validation.user_no_permission_edit')]);

            return;
        }

        if ($user) {
            $this->editForm->setUser($user);
            $this->showEditModal = true;
        }
    }

    #[On('openBulkStatusModal')]
    public function bulkChangeStatus(array $ids): void
    {
        $this->showBulkStatusModal = true;
        $this->bulkSelectedIds = $ids;
    }
}
