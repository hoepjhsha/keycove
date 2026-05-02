<?php

namespace App\Livewire\Admin\Table\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Livewire\Admin\Action\User\UserIndex;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'userTable';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = User::query()
            ->where('id', '!=', auth('admin')->id());

        $currentUserRole = auth('admin')->user()->role;
        // Admins can only see Users and Sellers
        if ($currentUserRole !== UserRole::SuperAdmin) {
            $query->whereNotIn('role', [UserRole::SuperAdmin, UserRole::Admin]);
        } else {
            // SuperAdmins cannot see other SuperAdmins in the table
            $query->where('role', '!=', UserRole::SuperAdmin);
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('role_label', function (User $model) {
                $role = $model->role;
                $labelText = method_exists($role, 'label') ? $role->label() : $role->name;

                $colorClass = match ($role) {
                    UserRole::User       => 'bg-blue-500/10 text-blue-500',
                    UserRole::Seller     => 'bg-indigo-500/10 text-indigo-500',
                    UserRole::Admin      => 'bg-purple-500/10 text-purple-500',
                    UserRole::SuperAdmin => 'bg-emerald-500/10 text-emerald-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('status_label', function (User $model) {
                $status = $model->status;
                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    UserStatus::Active   => 'bg-green-500/10 text-green-500',
                    UserStatus::Inactive => 'bg-gray-500/10 text-gray-500',
                    UserStatus::Blocked  => 'bg-yellow-500/10 text-yellow-500',
                    UserStatus::Deleted  => 'bg-red-500/10 text-red-500',
                    default              => 'bg-primary-500/10 text-primary-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (User $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (User $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')->index(),
            Column::make(__('admin.common.username'), 'username')->sortable()->searchable(),
            Column::make(__('admin.common.email'), 'email')->sortable()->searchable(),
            Column::make(__('admin.common.role'), 'role_label', 'role')->sortable(),
            Column::make(__('admin.common.status'), 'status_label', 'status')->sortable(),
            Column::make(__('admin.common.created_at_short'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin.common.action')),
        ];
    }

    public function filters(): array
    {
        $currentUserRole = auth('admin')->user()->role;
        $roles = collect(UserRole::cases());

        if ($currentUserRole !== UserRole::SuperAdmin) {
            $roles = $roles->filter(fn ($r) => ! in_array($r, [UserRole::Admin, UserRole::SuperAdmin]));
        } else {
            $roles = $roles->filter(fn ($r) => $r !== UserRole::SuperAdmin);
        }

        return [
            Filter::inputText('username')->operators(['contains']),
            Filter::inputText('email')->operators(['contains']),

            Filter::multiSelect('role', 'role')
                ->dataSource($roles->map(fn ($role) => [
                    'id'   => $role->value,
                    'name' => method_exists($role, 'label') ? $role->label() : $role->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            Filter::multiSelect('status', 'status')
                ->dataSource(collect(UserStatus::cases())->map(fn ($status) => [
                    'id'   => $status->value,
                    'name' => method_exists($status, 'label') ? $status->label() : $status->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            // TODO: Datepicker disabled - need to fix date range
            //             Filter::datepicker('created_at_formatted', 'created_at'),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('create')
                ->slot(__('admin.common.create'))
                ->class('bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded text-sm transition-colors duration-200')
                ->dispatch('openCreateModal', []),

            Button::add('bulk-delete')
                ->slot(__('admin.common.bulk_delete').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-4 border border-red-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('bulkDelete', []),

            Button::add('bulk-status')
                ->slot(__('admin.common.change_status').' (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-2 px-4 border border-yellow-500 hover:border-transparent rounded text-sm ml-2 transition-colors duration-200')
                ->dispatch('triggerBulkStatus', []),
        ];
    }

    public function actions(User $row): array
    {
        $deleteClass = $row->status === UserStatus::Deleted ? 'hidden' : '';

        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => __('admin.common.view_details'),
                ])
                ->dispatch('viewUser', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('text-blue-600 hover:text-blue-800 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.edit'),
                ])
                ->dispatch('editUser', ['rowId' => $row->id]),

            Button::add('toggle-block')
                ->slot($row->status === UserStatus::Blocked
                    ? '<i class="fa-solid fa-circle-check text-green-500 hover:text-green-800"></i>'
                    : '<i class="fa-solid fa-ban text-yellow-500 hover:text-yellow-800"></i>')
                ->id()
                ->class('px-1 py-1 transition-all hover:scale-110 text-lg '.$deleteClass)
                ->attributes([
                    'x-tooltip' => $row->status === UserStatus::Blocked ? __('admin.common.unblock_now') : __('admin.common.block_now'),
                ])
                ->dispatch('toggleBlock', ['rowId' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash-can"></i>')
                ->id()
                ->class('text-red-500 hover:text-red-700 px-1 py-1 transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => __('admin.common.delete'),
                ])
                ->dispatch('deleteUser', ['rowId' => $row->id]),

            Button::add('revertDelete')
                ->slot('<i class="fa-solid fa-rotate-left"></i>')
                ->id()
                ->class('text-yellow-500 hover:text-yellow-700 px-1 py-1 transition-all hover:scale-110 '.($row->status === UserStatus::Deleted ? '' : 'hidden'))
                ->attributes([
                    'x-tooltip' => __('admin.common.restore'),
                ])
                ->dispatch('revertDelete', ['rowId' => $row->id]),
        ];
    }

    #[On('toggleBlock')]
    public function toggleBlock($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_change_status'),
            'text'   => __('admin.swal.change_status_text', ['name' => __('admin.common.user')]),
            'method' => 'performToggleBlock',
            'id'     => $rowId,
        ]);
    }

    #[On('performToggleBlock')]
    public function performToggleBlock($id): void
    {
        $user = User::findOrFail($id);

        $currentUserRole = auth('admin')->user()->role;
        if ($currentUserRole !== UserRole::SuperAdmin && in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
            $this->dispatch('swal:error', ['message' => __('admin.validation.user_no_permission_modify')]);

            return;
        }

        $user->status = match ($user->status) {
            UserStatus::Blocked => UserStatus::Active,
            default             => UserStatus::Blocked,
        };
        $user->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.status_changed', ['Name' => __('admin.common.user')])]);
    }

    #[On('deleteUser')]
    public function deleteUser($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_delete', ['name' => __('admin.common.user')]),
            'text'   => __('admin.swal.delete_text', ['name' => __('admin.common.user')]),
            'method' => 'performDelete',
            'id'     => $rowId,
        ]);
    }

    #[On('performDelete')]
    public function performDelete($id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);

            $currentUserRole = auth('admin')->user()->role;
            if ($currentUserRole !== UserRole::SuperAdmin && in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
                $this->dispatch('swal:error', ['message' => __('admin.validation.user_no_permission_delete')]);

                return;
            }

            $user->status = UserStatus::Deleted;
            $user->save();

            $user->delete();
        });

        $this->dispatch('swal:success', ['message' => __('admin.messages.deleted', ['Name' => __('admin.common.user')])]);
    }

    #[On('revertDelete')]
    public function revertDelete($rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.confirm_restore', ['name' => __('admin.common.user')]),
            'text'   => __('admin.swal.restore_text', ['name' => __('admin.common.user')]),
            'method' => 'performRevertDelete',
            'id'     => $rowId,
        ]);
    }

    #[On('performRevertDelete')]
    public function performRevertDelete($id): void
    {
        $user = User::withTrashed()->findOrFail($id);

        $currentUserRole = auth('admin')->user()->role;
        if ($currentUserRole !== UserRole::SuperAdmin && in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
            $this->dispatch('swal:error', ['message' => __('admin.validation.user_no_permission_modify')]);

            return;
        }

        $user->restore();
        $user->status = UserStatus::Inactive;
        $user->save();

        $this->dispatch('swal:success', ['message' => __('admin.messages.restored', ['Name' => __('admin.common.user')])]);
    }

    #[On('bulkDelete')]
    public function bulkDelete(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.common.user')])]);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title'  => __('admin.swal.delete_selected', ['count' => count($this->checkboxValues)]),
            'method' => 'performBulkDelete',
            'id'     => null,
        ]);
    }

    #[On('performBulkDelete')]
    public function performBulkDelete(): void
    {
        $alreadyDeletedExists = User::onlyTrashed()
            ->whereIn('id', $this->checkboxValues)
            ->exists();

        if ($alreadyDeletedExists) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.cannot_change_deleted_status')]);

            return;
        }

        $currentUserRole = auth('admin')->user()->role;

        $query = User::whereIn('id', $this->checkboxValues);
        if ($currentUserRole !== UserRole::SuperAdmin) {
            $query->whereNotIn('role', [UserRole::SuperAdmin, UserRole::Admin]);
            $invalidUsers = User::whereIn('id', $this->checkboxValues)
                ->whereIn('role', [UserRole::SuperAdmin, UserRole::Admin])->exists();

            if ($invalidUsers) {
                $this->dispatch('swal:error', ['message' => __('admin.validation.user_no_permission_delete')]);

                return;
            }
        }

        DB::transaction(function () use ($query) {
            $query->update(['status' => UserStatus::Deleted]);
            User::whereIn('id', $this->checkboxValues)->delete();
        });

        $this->dispatch('swal:success', ['message' => __('admin.messages.bulk_delete_completed')]);
    }

    #[On('triggerBulkStatus')]
    public function triggerBulkStatus(): void
    {
        if (empty($this->checkboxValues)) {
            $this->dispatch('swal:error', ['message' => __('admin.messages.select_at_least_one', ['name' => __('admin.common.user')])]);

            return;
        }

        $this->dispatch('openBulkStatusModal', ids: $this->checkboxValues)->to(UserIndex::class);
    }
}
