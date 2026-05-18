<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlatformPayoutItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformPayoutItem extends Model
{
    /** @use HasFactory<PlatformPayoutItemFactory> */
    use HasFactory;

    protected $fillable = [
        'platform_payout_id',
        'order_item_id',
        'amount',
        'profit_type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount'   => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(PlatformPayout::class, 'platform_payout_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
