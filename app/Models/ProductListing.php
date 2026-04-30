<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductListingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'variant_id',
        'seller_id',
        'display_name',
        'slug',
        'price',
        'stock_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'stock_count' => 'integer',
            'status'      => ProductListingStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductListing $listing): void {
            if (filled($listing->slug)) {
                return;
            }

            $listing->slug = static::generateSlug($listing);
        });
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function keys(): HasMany
    {
        return $this->hasMany(ProductKey::class, 'listing_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'listing_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'listing_id');
    }

    public static function generateSlug(ProductListing $listing): string
    {
        $listing->loadMissing(['variant.product', 'variant.region', 'variant.platform', 'variant.operatingSystem']);

        $base = Str::slug(implode(' ', array_filter([
            $listing->display_name ?: $listing->variant?->product?->name,
            $listing->variant?->edition,
            $listing->variant?->region?->slug,
            $listing->variant?->platform?->slug,
            $listing->variant?->operatingSystem?->slug,
        ])));

        if ($base === '') {
            $base = 'listing';
        }

        return static::ensureUniqueSlug($base, $listing->id);
    }

    protected static function ensureUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::limit($base, 240, '');
        $candidate = $slug;
        $suffix = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $candidate)
            ->exists()
        ) {
            $candidate = Str::limit($slug, 240, '').'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
