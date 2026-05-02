<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\RegionRepositoryInterface;
use App\Models\Region;

class RegionRepository extends Repository implements RegionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Region);
    }
}
