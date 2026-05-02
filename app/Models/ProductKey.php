<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductKeyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'key_code',
        'key_hash',
        'status',
        'order_item_id',
    ];

    protected function casts(): array
    {
        return [
            'key_code' => 'encrypted',
            'status'   => ProductKeyStatus::class,
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'listing_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
