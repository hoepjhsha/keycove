<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\GeneralStatus;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\WithFileUploads;

class ProductEditForm extends Form
{
    use WithFileUploads;

    public ?Product $product = null;

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
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

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

    public function setProduct(Product $product): void
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->publisher = $product->publisher;
        $this->developer = $product->developer;
        $this->release_date = $product->release_date?->format('Y-m-d');
        $this->description = $product->description;
        $this->status = $product->status->value;
        $this->categories = $product->categories->pluck('id')->toArray();

        if ($product->system_requirement) {
            $this->systemRequirements = collect($product->system_requirement)
                ->map(fn ($value, $key) => ['key' => $key, 'value' => $value])
                ->values()
                ->toArray();
        }
    }

    public function update(): bool
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Product::where('slug', $this->slug)->where('id', '!=', $this->product->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.slug' => 'Product already exists. Write your own slug or change product name',
            ]);
        }

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => 'Cannot set status to Deleted via update.',
            ]);
        }

        $imagePath = $this->product->image_thumbnail_path;
        if ($this->image) {
            $disk = config('filesystems.public_disk');

            if ($imagePath) {
                Storage::disk($disk)->delete($imagePath);
            }

            $imagePath = $this->image->store(
                'products/thumbnails',
                $disk,
            );
        }

        $requirements = collect($this->systemRequirements)
            ->filter(fn ($item) => ! empty($item['key']) && ! empty($item['value']))
            ->mapWithKeys(fn ($item) => [$item['key'] => $item['value']])
            ->all();

        $updated = $this->product->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'publisher' => $this->publisher,
            'developer' => $this->developer,
            'release_date' => $this->release_date,
            'description' => $this->description,
            'status' => $this->status,
            'image_thumbnail_path' => $imagePath,
            'system_requirement' => ! empty($requirements) ? $requirements : null,
        ]);

        if ($updated && ! empty($this->categories)) {
            $this->product->categories()->sync($this->categories);
        } elseif ($updated) {
            $this->product->categories()->detach();
        }

        return $updated;
    }
}
