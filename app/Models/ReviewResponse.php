<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewResponse extends Model
{
    use HasFactory;

    const null UPDATED_AT = null;

    protected $fillable = [
        'review_id',
        'replier_id',
        'content',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replier_id');
    }
}
