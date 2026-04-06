<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;

class ReviewRepository extends Repository implements ReviewRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Review);
    }
}
