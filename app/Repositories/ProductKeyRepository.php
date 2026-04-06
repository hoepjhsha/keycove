<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Models\ProductKey;

class ProductKeyRepository extends Repository implements ProductKeyRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ProductKey);
    }
}
