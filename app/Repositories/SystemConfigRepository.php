<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\SystemConfigRepositoryInterface;
use App\Models\SystemConfig;

class SystemConfigRepository extends Repository implements SystemConfigRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new SystemConfig);
    }
}
