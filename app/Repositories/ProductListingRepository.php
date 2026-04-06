<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Models\ProductListing;

class ProductListingRepository extends Repository implements ProductListingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ProductListing);
    }
}
