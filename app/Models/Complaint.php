<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'resolved_by',
        'complaint_code',
        'reason',
        'evidence',
        'status',
        'resolution_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'complaint_code' => 'string',
            'evidence'       => 'array',
            'status'         => ComplaintStatus::class,
            'resolved_at'    => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ComplaintMessage::class)->orderBy('created_at');
    }

    public function getRouteKeyName(): string
    {
        return 'complaint_code';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        return $query->where(function (Builder $query) use ($value): void {
            $query->where('complaint_code', $value)
                ->orWhere('id', $value);
        });
    }
}
