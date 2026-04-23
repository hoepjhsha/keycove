<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\SystemConfig;

use App\Livewire\Admin\Form\SystemConfig\SystemConfigCreateForm;
use App\Livewire\Admin\Form\SystemConfig\SystemConfigEditForm;
use App\Models\SystemConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage System Settings')]
class SystemConfigIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showViewModal = false;

    public ?array $viewData = null;

    public string $search = '';

    /** @var array<int, string> */
    public array $inlineValues = [];

    public SystemConfigCreateForm $createForm;

    public SystemConfigEditForm $editForm;

    public function mount(): void
    {
        $this->syncInlineValues();
    }

    #[Computed]
    public function configs(): Collection
    {
        return SystemConfig::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery->where('key', 'like', '%'.$this->search.'%')
                        ->orWhere('value', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('key')
            ->get();
    }

    public function createSystemConfig(): void
    {
        $result = $this->createForm->store();

        if ($result) {
            $this->reset('createForm');
            $this->syncInlineValues();

            sweetalert()->title('Success!')->showConfirmButton(false)->success('System setting created successfully');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to create system setting');
        }

        $this->showCreateModal = false;
    }

    public function updateSystemConfig(): void
    {
        $result = $this->editForm->update();

        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('System setting updated successfully');
        } else {
            sweetalert()->title('Error!')->showConfirmButton(false)->error('Failed to update system setting');
        }

        $this->showEditModal = false;
    }

    public function render(): View
    {
        return view('pages.admin.system-settings.index')->layout('components.layouts.dashboard');
    }

    #[On('openCreateModal')]
    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    #[On('viewSystemConfig')]
    public function viewSystemConfig(int $rowId): void
    {
        $systemConfig = SystemConfig::find($rowId);

        if ($systemConfig) {
            $this->viewData = [
                'id'          => $systemConfig->id,
                'key'         => $systemConfig->key,
                'value'       => $systemConfig->value,
                'description' => $systemConfig->description,
            ];

            $this->showViewModal = true;
        }
    }

    #[On('editSystemConfig')]
    public function editSystemConfig(int $rowId): void
    {
        $systemConfig = SystemConfig::find($rowId);

        if ($systemConfig) {
            $this->editForm->setSystemConfig($systemConfig);
            $this->showEditModal = true;
        }
    }

    #[On('deleteSystemConfig')]
    public function deleteSystemConfig(int $rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Delete System Setting?',
            'text'   => 'Are you sure you want to delete this system setting? This action cannot be undone.',
            'method' => 'performDeleteSystemConfig',
            'id'     => $rowId,
        ]);
    }

    #[On('performDeleteSystemConfig')]
    public function performDeleteSystemConfig(int $id): void
    {
        $systemConfig = SystemConfig::findOrFail($id);
        $systemConfig->delete();

        unset($this->inlineValues[$id]);

        sweetalert()->title('Success!')->showConfirmButton(false)->success('System setting deleted successfully');
    }

    public function saveInlineValue(int $id): void
    {
        $value = $this->inlineValues[$id] ?? '';

        Validator::make([
            'value' => $value,
        ], [
            'value' => ['required', 'string', 'max:255'],
        ])->validate();

        $systemConfig = SystemConfig::findOrFail($id);
        $systemConfig->update(['value' => $value]);

        sweetalert()->title('Success!')->showConfirmButton(false)->success('System setting updated successfully');
    }

    public function isJsonValue(?string $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }

    public function prettyJson(?string $value): string
    {
        if (! $this->isJsonValue($value)) {
            return (string) $value;
        }

        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: (string) $value;
    }

    private function syncInlineValues(): void
    {
        $this->inlineValues = SystemConfig::query()
            ->orderBy('key')
            ->pluck('value', 'id')
            ->map(fn (string $value): string => $value)
            ->all();
    }
}
