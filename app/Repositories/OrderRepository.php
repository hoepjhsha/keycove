<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;

class OrderRepository extends Repository implements OrderRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Order);
    }
}
