<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ReviewResponseRepositoryInterface;
use App\Models\ReviewResponse;

class ReviewResponseRepository extends Repository implements ReviewResponseRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ReviewResponse);
    }
}
