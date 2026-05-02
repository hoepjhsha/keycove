<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Product);
    }
}
