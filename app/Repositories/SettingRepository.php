<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Models\Setting;

class SettingRepository extends Repository implements SettingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Setting);
    }
}
