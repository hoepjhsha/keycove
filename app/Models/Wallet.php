<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'type',
        'code',
        'balance',
        'holding',
    ];

    protected function casts(): array
    {
        return [
            'type'    => WalletType::class,
            'balance' => 'decimal:2',
            'holding' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function internalEntries(): HasMany
    {
        return $this->hasMany(InternalWalletEntry::class);
    }
}
