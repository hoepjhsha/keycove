<div>
    @section('pageTitle', 'Manage Users')

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => 'Management', 'url' => 'javascript:void(0)'],
            ['label' => 'Users', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="bg-white dark:bg-slate-800 shadow rounded-md w-full relative">
        <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
            <h4 class="font-medium">Manage Users</h4>
        </div>
        <div class="flex-auto p-4">
            <livewire:admin.table.user.user-table />
        </div>
    </div>

    <x-reusable.modal wire:model="showCreateModal" title="Create new User" max-width="2xl">
        <form id="createUserForm" class="space-y-4" wire:submit="createUser">
            <div class="mb-2">
                <label for="username" class="font-medium text-sm text-slate-600 dark:text-slate-400">Username<span class="text-red-400">*</span></label>
                <input wire:model="createForm.username" type="text" id="username" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500" required>
                @error('createForm.username')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="email" class="font-medium text-sm text-slate-600 dark:text-slate-400">Email<span class="text-red-400">*</span></label>
                <input wire:model="createForm.email" type="email" id="email" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500" required>
                @error('createForm.email')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="password" class="font-medium text-sm text-slate-600 dark:text-slate-400">Password<span class="text-red-400">*</span></label>
                <input wire:model="createForm.password" type="password" id="password" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500" required minlength="8">
                @error('createForm.password')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="create_role" class="font-medium text-sm text-slate-600 dark:text-slate-400">Role <span class="text-red-400">*</span></label>
                <select wire:model="createForm.role" id="create_role" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500" required>
                    <option value="">-- Select Role --</option>
                    @foreach(\App\Enums\UserRole::cases() as $roleEnum)
                        @if($roleEnum !== \App\Enums\UserRole::SuperAdmin && (auth('admin')->user()->role === \App\Enums\UserRole::SuperAdmin || !in_array($roleEnum, [\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Admin])))
                            <option value="{{ $roleEnum->value }}">{{ $roleEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('createForm.role')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="create_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">Status <span class="text-red-400">*</span></label>
                <select wire:model="createForm.status" id="create_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 hover:border-slate-400 focus:border-primary-500 dark:focus:border-primary-500" required>
                    <option value="">-- Select Status --</option>
                    @foreach(\App\Enums\UserStatus::cases() as $statusEnum)
                        @if($statusEnum !== \App\Enums\UserStatus::Deleted)
                            <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('createForm.status')
                    <small class="error block text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex items-center justify-end space-x-2">
                <button wire:target="createUser" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:border-blue-700 text-sm font-medium py-1 px-3 rounded mb-1">Submit</button>
                <button wire:click="$set('showCreateModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:border-gray-700 text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showViewModal" title="User Details" max-width="2xl">
        @if($viewData)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 text-sm border border-slate-200 dark:border-slate-700">
                    <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">ID</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">#{{ $viewData['id'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Username</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['username'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Email</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['email'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Role</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['role_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{!! $viewData['status_label'] !!}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Created At</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['created_at'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 py-3 border-b-0">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">Updated At</dt>
                            <dd class="col-span-2 text-slate-900 dark:text-white">{{ $viewData['updated_at'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif
        <x-slot:footer>
            <button wire:click="$set('showViewModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors inline-flex items-center">
                <i class="fa-solid fa-xmark mr-2"></i> Close
            </button>
        </x-slot:footer>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showEditModal" title="Edit User #{{ $editForm->user?->id }}" max-width="2xl">
        <form id="editUserForm" class="space-y-4" wire:submit="updateUser">
            <div class="mb-2">
                <label for="edit_username" class="font-medium text-sm text-slate-600 dark:text-slate-400">Username<span class="text-red-400">*</span></label>
                <input wire:model="editForm.username" type="text" id="edit_username" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0" required>
                @error('editForm.username')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="edit_email" class="font-medium text-sm text-slate-600 dark:text-slate-400">Email<span class="text-red-400">*</span></label>
                <input wire:model="editForm.email" type="email" id="edit_email" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0" required>
                @error('editForm.email')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="edit_password" class="font-medium text-sm text-slate-600 dark:text-slate-400">Password (Leave blank to keep current)</label>
                <input wire:model="editForm.password" type="password" id="edit_password" class="form-input w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0" minlength="8">
                @error('editForm.password')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="edit_role" class="font-medium text-sm text-slate-600 dark:text-slate-400">Role <span class="text-red-400">*</span></label>
                <select wire:model="editForm.role" id="edit_role" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0 disabled:bg-slate-100 disabled:dark:bg-slate-800 disabled:text-slate-500" required @if($editForm->user?->role === \App\Enums\UserRole::Admin) disabled @endif>
                    <option value="">-- Select Role --</option>
                    @foreach(\App\Enums\UserRole::cases() as $roleEnum)
                        @if($roleEnum !== \App\Enums\UserRole::SuperAdmin && (auth('admin')->user()->role === \App\Enums\UserRole::SuperAdmin || !in_array($roleEnum, [\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Admin])))
                            @if($roleEnum !== \App\Enums\UserRole::Admin || $editForm->user?->role === \App\Enums\UserRole::Admin)
                                <option value="{{ $roleEnum->value }}">{{ $roleEnum->label() }}</option>
                            @endif
                        @endif
                    @endforeach
                </select>
                @error('editForm.role')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-2">
                <label for="edit_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">Status <span class="text-red-400">*</span></label>
                <select wire:model="editForm.status" id="edit_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-1 focus:outline-none focus:ring-0" required>
                    @foreach(\App\Enums\UserStatus::cases() as $statusEnum)
                        @if($statusEnum !== \App\Enums\UserStatus::Deleted)
                            <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('editForm.status')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex items-center justify-end space-x-2">
                <button wire:target="updateUser" type="submit" class="inline-block focus:outline-none text-blue-500 hover:bg-blue-500 hover:text-white bg-transparent border border-blue-200 dark:border-blue-700 text-sm font-medium py-1 px-3 rounded mb-1">Update</button>
                <button wire:click="$set('showEditModal', false)" type="button" class="inline-block focus:outline-none text-red-500 hover:bg-red-500 hover:text-white bg-transparent border border-gray-200 dark:border-gray-700 text-sm font-medium py-1 px-3 rounded mb-1">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    <x-reusable.modal wire:model="showBulkStatusModal" title="Change Status for Selected Users" max-width="md">
        <form id="bulkStatusForm" class="space-y-4" wire:submit="bulkChangeStatusUser">
            <div class="mb-2">
                <label for="bulk_status" class="font-medium text-sm text-slate-600 dark:text-slate-400">
                    Select New Status <span class="text-red-400">*</span>
                </label>
                <select wire:model="bulkChangeStatusForm.status" id="bulk_status" class="form-select w-full rounded-md mt-1 border border-slate-300/60 dark:border-slate-700 dark:text-slate-300 bg-transparent px-3 py-2 focus:outline-none focus:ring-0" required>
                    <option value="">-- Select Status --</option>
                    @foreach(\App\Enums\UserStatus::cases() as $statusEnum)
                        @if($statusEnum !== \App\Enums\UserStatus::Deleted)
                            <option value="{{ $statusEnum->value }}">{{ $statusEnum->label() }}</option>
                        @endif
                    @endforeach
                </select>
                @error('bulkChangeStatusForm.status')
                    <small class="text-red-500 text-xs">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex items-center justify-end space-x-2 mt-6">
                <button wire:target="bulkChangeStatusUser" type="submit" class="inline-block focus:outline-none text-yellow-600 hover:bg-yellow-500 hover:text-white bg-transparent border border-yellow-400 dark:border-yellow-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">Apply Status</button>
                <button wire:click="$set('showBulkStatusModal', false)" type="button" class="inline-block focus:outline-none text-slate-500 hover:bg-slate-500 hover:text-white bg-transparent border border-slate-300 dark:border-slate-600 text-sm font-medium py-1 px-3 rounded mb-1 transition-colors">Cancel</button>
            </div>
        </form>
    </x-reusable.modal>

    @push('scripts')
        <x-admin.swal-listener table-name="userTable" />
    @endpush
</div>
