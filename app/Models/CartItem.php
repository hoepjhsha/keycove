<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_item_code',
        'cart_id',
        'listing_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'cart_item_code' => 'string',
            'quantity'       => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CartItem $cartItem): void {
            if (filled($cartItem->cart_item_code)) {
                return;
            }

            $cartItem->cart_item_code = static::generateCartItemCode();
        });
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'listing_id');
    }

    protected static function generateCartItemCode(): string
    {
        return 'CI-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
