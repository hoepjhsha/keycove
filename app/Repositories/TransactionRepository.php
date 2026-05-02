<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction;

class TransactionRepository extends Repository implements TransactionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Transaction);
    }
}
