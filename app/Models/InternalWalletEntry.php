<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InternalWalletDirection;
use App\Enums\InternalWalletEntryType;
use App\Enums\TransactionStatus;
use Database\Factories\InternalWalletEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InternalWalletEntry extends Model
{
    /** @use HasFactory<InternalWalletEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'order_id',
        'source_type',
        'source_id',
        'type',
        'direction',
        'amount',
        'status',
        'affects_balance',
        'idempotency_key',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type'            => InternalWalletEntryType::class,
            'direction'       => InternalWalletDirection::class,
            'amount'          => 'decimal:2',
            'status'          => TransactionStatus::class,
            'affects_balance' => 'boolean',
            'metadata'        => 'array',
            'occurred_at'     => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
