<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\EscrowRepositoryInterface;
use App\Models\Escrow;

class EscrowRepository extends Repository implements EscrowRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Escrow);
    }
}
