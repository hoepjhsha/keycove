<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository extends Repository implements CategoryRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Category);
    }
}
