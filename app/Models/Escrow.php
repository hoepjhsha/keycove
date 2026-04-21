<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'amount',
        'release_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            // 'amount' => 'decimal:2',
            'release_date' => 'datetime',
            'status' => EscrowStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items()
    {
        return OrderItem::where('order_id', $this->order_id)
            ->whereHas('listing', function ($query) {
                $query->where('seller_id', $this->seller_id);
            })->get();
    }
}
