<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductListingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'variant_id',
        'seller_id',
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
}
