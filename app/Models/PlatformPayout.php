<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformPayoutStatus;
use Database\Factories\PlatformPayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformPayout extends Model
{
    /** @use HasFactory<PlatformPayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'payout_code',
        'period_start',
        'period_end',
        'settlement_cutoff_at',
        'amount',
        'status',
        'bank_name',
        'bank_code',
        'bank_account_number',
        'bank_account_name',
        'processed_at',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'period_start'         => 'date',
            'period_end'           => 'date',
            'settlement_cutoff_at' => 'datetime',
            'amount'               => 'decimal:2',
            'status'               => PlatformPayoutStatus::class,
            'bank_account_number'  => 'encrypted',
            'processed_at'         => 'datetime',
            'metadata'             => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlatformPayoutItem::class);
    }
}
