<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * @param  array{name: string, slug?: string, publisher?: ?string, developer?: ?string, release_date?: ?string, description?: ?string, status: int, categories?: array<int, int|string>, image?: mixed, submitted_by_seller_id?: ?int, system_requirements?: ?array<string, string>}  $data
     */
    public function create(array $data, string $slugErrorKey = 'slug'): Product
    {
        $slug = $this->normalizeSlug($data['name'], $data['slug'] ?? null);

        $this->ensureUniqueSlug($slug, null, $slugErrorKey);

        $imagePath = $this->storeImage($data['image'] ?? null);

        return DB::transaction(function () use ($data, $slug, $imagePath): Product {
            /** @var Product $product */
            $product = $this->products->create([
                'submitted_by_seller_id' => $data['submitted_by_seller_id'] ?? null,
                'name'                   => $data['name'],
                'slug'                   => $slug,
                'publisher'              => $data['publisher'] ?? null,
                'developer'              => $data['developer'] ?? null,
                'release_date'           => $data['release_date'] ?? null,
                'description'            => $data['description'] ?? null,
                'status'                 => $data['status'],
                'image_thumbnail_path'   => $imagePath,
                'system_requirement'     => $data['system_requirements'] ?? null,
            ]);

            $this->syncCategories($product, $data['categories'] ?? []);

            return $product;
        });
    }

    /**
     * @param  array{name: string, slug?: string, publisher?: ?string, developer?: ?string, release_date?: ?string, description?: ?string, status: int, categories?: array<int, int|string>, image?: mixed, system_requirements?: ?array<string, string>}  $data
     */
    public function update(
        Product $product,
        array $data,
        string $slugErrorKey = 'slug',
        string $statusErrorKey = 'status',
    ): bool {
        $status = GeneralStatus::from((int) $data['status']);

        if ($status === GeneralStatus::Deleted) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_update'),
            ]);
        }

        $slug = $this->normalizeSlug($data['name'], $data['slug'] ?? null);

        $this->ensureUniqueSlug($slug, $product->id, $slugErrorKey);

        $imagePath = $product->image_thumbnail_path;

        if (($data['image'] ?? null) !== null) {
            $imagePath = $this->storeImage($data['image'], $product->image_thumbnail_path);
        }

        return DB::transaction(function () use ($product, $data, $slug, $status, $imagePath): bool {
            $updated = $product->update([
                'name'                 => $data['name'],
                'slug'                 => $slug,
                'publisher'            => $data['publisher'] ?? null,
                'developer'            => $data['developer'] ?? null,
                'release_date'         => $data['release_date'] ?? null,
                'description'          => $data['description'] ?? null,
                'status'               => $status,
                'image_thumbnail_path' => $imagePath,
                'system_requirement'   => $data['system_requirements'] ?? null,
            ]);

            if ($updated) {
                $this->syncCategories($product, $data['categories'] ?? []);
            }

            return $updated;
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->status = GeneralStatus::Deleted;
            $product->save();
            $product->delete();
        });
    }

    public function restore(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->restore();
            $product->status = GeneralStatus::Inactive;
            $product->save();
        });
    }

    private function ensureUniqueSlug(string $slug, ?int $ignoreProductId, string $errorKey): void
    {
        if (! $this->products->slugExists($slug, $ignoreProductId)) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => __('admin.validation.duplicate_product_slug'),
        ]);
    }

    /**
     * @param  array<int, int|string>  $categories
     */
    private function syncCategories(Product $product, array $categories): void
    {
        if ($categories === []) {
            $product->categories()->detach();

            return;
        }

        $product->categories()->sync($categories);
    }

    private function normalizeSlug(string $name, ?string $slug): string
    {
        $normalizedSlug = trim((string) $slug);

        if ($normalizedSlug !== '') {
            return $normalizedSlug;
        }

        return Str::slug($name);
    }

    private function storeImage(mixed $image, ?string $currentPath = null): ?string
    {
        if ($image === null) {
            return $currentPath;
        }

        $disk = (string) config('filesystems.public_disk');
        $storedPath = $image->store('products/thumbnails', $disk);

        if ($currentPath !== null && $currentPath !== '') {
            Storage::disk($disk)->delete($currentPath);
        }

        return $storedPath;
    }
}
