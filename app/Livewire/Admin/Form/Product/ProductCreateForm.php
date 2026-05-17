<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\GeneralStatus;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\WithFileUploads;

class ProductCreateForm extends Form
{
    use WithFileUploads;

    #[Validate([
        'required',
        'string',
        'max:255',
    ])]
    public string $name = '';

    #[Validate([
        'nullable',
        'string',
        'regex:/^[a-z]([a-z0-9-]*[a-z0-9])?$/',
        'max:255',
    ])]
    public string $slug = '';

    #[Validate([
        'nullable',
        'string',
        'max:255',
    ])]
    public ?string $publisher = null;

    #[Validate([
        'nullable',
        'string',
        'max:255',
    ])]
    public ?string $developer = null;

    #[Validate([
        'nullable',
        'date_format:Y-m-d',
    ])]
    public ?string $release_date = null;

    #[Validate([
        'nullable',
        'string',
    ])]
    public ?string $description = null;

    #[Validate([
        'required',
        'integer',
        new Enum(GeneralStatus::class),
    ])]
    public int $status = 0;

    #[Validate([
        'nullable',
        'array',
        'exists:categories,id',
    ])]
    public array $categories = [];

    #[Validate([
        'nullable',
        'image',
        'max:2048',
    ])]
    public $image = null;

    #[Validate([
        'nullable',
        'integer',
        'exists:sellers,id',
    ])]
    public ?int $submitted_by_seller_id = null;

    public array $systemRequirements = [
        ['key' => '', 'value' => ''],
    ];

    public function addSystemRequirement(): void
    {
        $this->systemRequirements[] = ['key' => '', 'value' => ''];
    }

    public function removeSystemRequirement(int $index): void
    {
        unset($this->systemRequirements[$index]);
        $this->systemRequirements = array_values($this->systemRequirements);
    }

    /**
     * @return array{name: string, slug: string, publisher: ?string, developer: ?string, release_date: ?string, description: ?string, status: int, categories: array<int, int|string>, image: mixed, submitted_by_seller_id: ?int, system_requirements: ?array<string, string>}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'                   => $this->name,
            'slug'                   => $this->slug,
            'publisher'              => $this->publisher,
            'developer'              => $this->developer,
            'release_date'           => $this->release_date,
            'description'            => $this->description,
            'status'                 => $this->status,
            'categories'             => $this->categories,
            'image'                  => $this->image,
            'submitted_by_seller_id' => $this->submitted_by_seller_id,
            'system_requirements'    => $this->normalizeSystemRequirements(),
        ];
    }

    public function store(): Product
    {
        return app(ProductService::class)->create($this->validatedData(), 'createForm.slug');
    }

    /**
     * @return ?array<string, string>
     */
    private function normalizeSystemRequirements(): ?array
    {
        $requirements = collect($this->systemRequirements)
            ->filter(fn (array $item): bool => ! empty($item['key']) && ! empty($item['value']))
            ->mapWithKeys(fn (array $item): array => [$item['key'] => $item['value']])
            ->all();

        return $requirements !== [] ? $requirements : null;
    }
}
