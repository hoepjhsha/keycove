<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ComplaintRepositoryInterface;
use App\Models\Complaint;

class ComplaintRepository extends Repository implements ComplaintRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Complaint);
    }
}
