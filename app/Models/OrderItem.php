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
        'product_name_snapshot',
        'quantity',
        'unit_price',
        'subtotal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal'   => 'decimal:2',
            'status'     => OrderStatus::class,
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

    public function keys(): HasMany
    {
        return $this->hasMany(ProductKey::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function complaint(): HasOne
    {
        return $this->hasOne(Complaint::class);
    }
}
