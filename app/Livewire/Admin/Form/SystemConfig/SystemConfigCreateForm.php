<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\SystemConfig;

use App\Models\SystemConfig;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SystemConfigCreateForm extends Form
{
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

    public function store(): ?SystemConfig
    {
        $this->validate();

        $this->key = Str::lower(trim($this->key));

        if (SystemConfig::where('key', $this->key)->exists()) {
            throw ValidationException::withMessages([
                'createForm.key' => 'System setting already exists. Use a different key.',
            ]);
        }

        return SystemConfig::create([
            'key'         => $this->key,
            'value'       => $this->value,
            'description' => filled($this->description) ? $this->description : null,
        ]);
    }
}
