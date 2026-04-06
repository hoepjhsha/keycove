<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new User);
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmailOrUsername(string $identifier): ?User
    {
        return $this->newQuery()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function markEmailAsVerified(User $user): bool
    {
        return $user->update(['email_verified_at' => now()]);
    }
}
