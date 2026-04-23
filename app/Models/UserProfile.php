<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'avatar',
        'dob',
        'gender',
        'phone_number',
        'bio',
    ];

    protected function casts(): array
    {
        return [
            'dob'    => '',
            'gender' => Gender::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
