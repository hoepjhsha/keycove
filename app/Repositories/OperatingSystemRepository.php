<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\OperatingSystemRepositoryInterface;
use App\Models\OperatingSystem;

class OperatingSystemRepository extends Repository implements OperatingSystemRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new OperatingSystem);
    }
}
