<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'wallet_id',
        'type',
        'payment_info',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payment_info' => 'array',
            //            'amount' => 'decimal:2',
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
