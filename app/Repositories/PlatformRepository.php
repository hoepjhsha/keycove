<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Models\Platform;

class PlatformRepository extends Repository implements PlatformRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Platform);
    }
}
