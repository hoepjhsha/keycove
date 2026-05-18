<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submitted_by_seller_id',
        'approved_by',
        'name',
        'slug',
        'image_thumbnail_path',
        'publisher',
        'developer',
        'release_date',
        'description',
        'system_requirement',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'release_date'       => 'date',
            'system_requirement' => 'array',
            'status'             => GeneralStatus::class,
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryProduct::class)
            ->withPivot(['sort_order', 'is_featured'])
            ->withTimestamps();
    }

    public function getDisplayCategoriesAttribute()
    {
        if ($this->categories->isEmpty()) {
            return collect([
                (object) [
                    'name' => 'Uncategorized',
                    'slug' => 'uncategorized',
                ],
            ]);
        }

        return $this->categories;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function listings(): HasManyThrough
    {
        return $this->hasManyThrough(ProductListing::class, ProductVariant::class, 'product_id', 'variant_id');
    }

    public function submittedBySeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'submitted_by_seller_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::saved(function (Product $product): void {
            if ($product->submitted_by_seller_id === null) {
                return;
            }

            if ($product->status === GeneralStatus::Active && $product->approved_by === null) {
                $product->forceFill([
                    'approved_by' => auth('admin')->id(),
                ])->saveQuietly();
            }

            $product->syncChildStatuses();
        });
    }

    public function syncChildStatuses(): void
    {
        $variantStatus = $this->status === GeneralStatus::Active
            ? ProductVariantStatus::Active
            : ProductVariantStatus::Draft;

        $listingStatus = $this->status === GeneralStatus::Active
            ? ProductListingStatus::Active
            : ProductListingStatus::Draft;

        $this->variants()->update([
            'status' => $variantStatus->value,
        ]);

        ProductListing::query()
            ->whereHas('variant', function ($query): void {
                $query->where('product_id', $this->id);
            })
            ->update([
                'status' => $listingStatus->value,
            ]);
    }
}
