<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'amount',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'requested_by',
        'processed_by',
        'processed_at',
        'reject_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount'              => 'decimal:2',
            'bank_account_number' => 'encrypted',
            'status'              => WithdrawStatus::class,
            'processed_at'        => 'datetime',
            'metadata'            => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
