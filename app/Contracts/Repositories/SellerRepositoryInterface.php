<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface SellerRepositoryInterface extends RepositoryInterface
{
    /**
     * @return EloquentCollection<int, Seller>
     */
    public function getApprovedWithUser(): EloquentCollection;
}
