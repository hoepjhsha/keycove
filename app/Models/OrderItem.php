<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'listing_id',
        'seller_id',
        'product_name_snapshot',
        'variant_snapshot',
        'quantity',
        'unit_price',
        'subtotal',
        'platform_fee',
        'seller_amount',
        'status',
        'buyer_key_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'variant_snapshot'    => 'array',
            'quantity'            => 'integer',
            'unit_price'          => 'decimal:2',
            'subtotal'            => 'decimal:2',
            'platform_fee'        => 'decimal:2',
            'seller_amount'       => 'decimal:2',
            'status'              => OrderStatus::class,
            'buyer_key_viewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'listing_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function keys(): HasMany
    {
        return $this->hasMany(ProductKey::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }

    public function complaint(): HasOne
    {
        return $this->hasOne(Complaint::class);
    }
}
