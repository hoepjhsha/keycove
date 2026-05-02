<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\SellerRepositoryInterface;
use App\Models\Seller;

class SellerRepository extends Repository implements SellerRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Seller);
    }
}
