<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ComplaintMessageRepositoryInterface;
use App\Models\ComplaintMessage;

class ComplaintMessageRepository extends Repository implements ComplaintMessageRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ComplaintMessage);
    }
}
