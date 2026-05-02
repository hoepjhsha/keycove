<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Models\Wallet;

class WalletRepository extends Repository implements WalletRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Wallet);
    }
}
