<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\SystemConfig;

use App\Models\SystemConfig;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SystemConfigEditForm extends Form
{
    public ?SystemConfig $systemConfig = null;

    #[Validate([
        'required',
        'string',
        'max:255',
        'regex:/^[a-z0-9_]+$/',
    ])]
    public string $key = '';

    #[Validate([
        'required',
        'string',
        'max:255',
    ])]
    public string $value = '';

    #[Validate([
        'nullable',
        'string',
    ])]
    public ?string $description = null;

    public function setSystemConfig(SystemConfig $systemConfig): void
    {
        $this->systemConfig = $systemConfig;
        $this->key = $systemConfig->key;
        $this->value = $systemConfig->value;
        $this->description = $systemConfig->description;
    }

    public function update(): bool
    {
        $this->validate();

        return $this->systemConfig->update([
            'key'         => $this->systemConfig->key,
            'value'       => $this->value,
            'description' => filled($this->description) ? $this->description : null,
        ]);
    }
}
