<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use HasFactory;

    const null UPDATED_AT = null;

    protected $fillable = [
        'order_item_id',
        'reason',
        'evidence',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'status' => ComplaintStatus::class,
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ComplaintMessage::class);
    }
}
