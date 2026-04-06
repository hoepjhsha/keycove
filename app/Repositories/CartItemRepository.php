<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\CartItemRepositoryInterface;
use App\Models\CartItem;

class CartItemRepository extends Repository implements CartItemRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new CartItem);
    }
}
