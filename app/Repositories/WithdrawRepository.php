<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\WithdrawRepositoryInterface;
use App\Models\Withdraw;

class WithdrawRepository extends Repository implements WithdrawRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Withdraw);
    }
}
