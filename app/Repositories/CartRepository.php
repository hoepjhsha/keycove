<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\Cart;

class CartRepository extends Repository implements CartRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Cart);
    }
}
