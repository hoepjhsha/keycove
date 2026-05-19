<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\SystemConfig;

use App\Enums\KycStatus;
use App\Models\Seller;
use App\Models\SystemConfig;
use App\Notifications\CommissionRateChangedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage System Settings')]
class SystemConfigIndex extends Component
{
    public bool $showViewModal = false;

    public ?array $viewData = null;

    public string $search = '';

    /** @var array<int, string> */
    public array $inlineValues = [];

    public function mount(): void
    {
        $this->syncInlineValues();
    }

    #[Computed]
    public function configs(): Collection
    {
        return SystemConfig::query()
            ->managed()
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

    public function render(): View
    {
        return view('pages.admin.system-settings.index')->layout('components.layouts.dashboard');
    }

    #[On('viewSystemConfig')]
    public function viewSystemConfig(int $rowId): void
    {
        $systemConfig = SystemConfig::query()->managed()->find($rowId);

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

    public function saveInlineValue(int $id): void
    {
        $systemConfig = SystemConfig::findOrFail($id);
        $field = 'inlineValues.'.$id;

        if (! SystemConfig::isManagedKey($systemConfig->key)) {
            throw ValidationException::withMessages([
                $field => __('admin.validation.unmanaged_system_config_key'),
            ]);
        }

        $value = $this->normaliseValue($systemConfig->key, (string) ($this->inlineValues[$id] ?? ''));

        Validator::make([
            'inlineValues' => [$id => $value],
        ], [
            $field => SystemConfig::validationRules($systemConfig->key, $value),
        ])->validate();

        $oldValue = $systemConfig->value;

        $systemConfig->update(['value' => $value]);
        $this->inlineValues[$id] = $value;

        if ($oldValue !== $value && SystemConfig::notifiesSellers($systemConfig->key)) {
            $this->notifyApprovedSellersOfCommissionRateChange($oldValue, $value);
        }

        sweetalert()->title(__('admin.common.success'))->showConfirmButton(false)->success(__('admin.messages.updated', ['Name' => __('admin.nav.system_settings')]));
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
            ->managed()
            ->orderBy('key')
            ->pluck('value', 'id')
            ->map(fn (string $value): string => $value)
            ->all();
    }

    private function normaliseValue(string $key, string $value): string
    {
        $value = trim($value);
        $definition = SystemConfig::managedDefinition($key);

        if (($definition['type'] ?? null) === SystemConfig::TYPE_BOOLEAN) {
            return strtolower($value);
        }

        return $value;
    }

    private function notifyApprovedSellersOfCommissionRateChange(string $oldRate, string $newRate): void
    {
        Seller::query()
            ->where('kyc_status', KycStatus::Approved)
            ->whereHas('user')
            ->with('user')
            ->chunkById(100, function (Collection $sellers) use ($oldRate, $newRate): void {
                Notification::send(
                    $sellers->pluck('user')->filter(),
                    new CommissionRateChangedNotification($oldRate, $newRate),
                );
            });
    }
}
