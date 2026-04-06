<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\ProductVariant;

class ProductVariantRepository extends Repository implements ProductVariantRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ProductVariant);
    }
}
