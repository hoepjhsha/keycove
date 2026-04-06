<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Models\OrderItem;

class OrderItemRepository extends Repository implements OrderItemRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new OrderItem);
    }
}
