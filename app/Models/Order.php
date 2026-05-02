<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'order_code',
        'total_price',
        'payment_method',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'total_price'    => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function escrow(): HasOneThrough
    {
        return $this->hasOneThrough(Escrow::class, OrderItem::class, 'order_id', 'order_item_id');
    }

    public function escrows(): HasManyThrough
    {
        return $this->hasManyThrough(Escrow::class, OrderItem::class, 'order_id', 'order_item_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (): OrderStatus => $this->resolveStatus(),
        );
    }

    public function resolveStatus(): OrderStatus
    {
        $aggregatedStatus = $this->getAttributeFromArray('aggregated_status');

        if ($aggregatedStatus !== null) {
            return OrderStatus::from((int) $aggregatedStatus);
        }

        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get(['status']);

        $statuses = $items
            ->pluck('status')
            ->filter(fn (mixed $status) => $status instanceof OrderStatus);

        foreach ($this->statusPriority() as $status) {
            if ($statuses->contains($status)) {
                return $status;
            }
        }

        return OrderStatus::Completed;
    }

    /**
     * @return list<OrderStatus>
     */
    private function statusPriority(): array
    {
        return [
            OrderStatus::PendingPayment,
            OrderStatus::Processing,
            OrderStatus::Disputing,
            OrderStatus::Cancelled,
            OrderStatus::Refunded,
            OrderStatus::Delivered,
            OrderStatus::Completed,
        ];
    }
}
