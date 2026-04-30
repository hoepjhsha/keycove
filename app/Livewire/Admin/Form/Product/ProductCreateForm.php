<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\GeneralStatus;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
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

    public function store(): Product
    {
        $this->validate();

        $imagePath = null;
        if ($this->image) {
            $disk = config('filesystems.public_disk');
            $imagePath = $this->image->store(
                'products/thumbnails',
                $disk,
            );
        }

        // Convert system requirements to JSON
        $requirements = collect($this->systemRequirements)
            ->filter(fn ($item) => ! empty($item['key']) && ! empty($item['value']))
            ->mapWithKeys(fn ($item) => [$item['key'] => $item['value']])
            ->all();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Product::where('slug', $this->slug)->exists()) {
            throw ValidationException::withMessages([
                'createForm.slug' => 'Product already exists. Write your own slug or change product name',
            ]);
        }

        $product = Product::create([
            'submitted_by_seller_id' => $this->submitted_by_seller_id,
            'name'                   => $this->name,
            'slug'                   => $this->slug,
            'publisher'              => $this->publisher,
            'developer'              => $this->developer,
            'release_date'           => $this->release_date,
            'description'            => $this->description,
            'status'                 => $this->status,
            'image_thumbnail_path'   => $imagePath,
            'system_requirement'     => ! empty($requirements) ? $requirements : null,
        ]);

        if (! empty($this->categories)) {
            $product->categories()->sync($this->categories);
        }

        return $product;
    }
}
