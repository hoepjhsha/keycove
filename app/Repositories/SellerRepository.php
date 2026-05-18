<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\SellerRepositoryInterface;
use App\Enums\KycStatus;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SellerRepository extends Repository implements SellerRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Seller);
    }

    /**
     * {@inheritDoc}
     */
    public function getApprovedWithUser(): EloquentCollection
    {
        return $this->newQuery()
            ->where('kyc_status', KycStatus::Approved)
            ->with('user')
            ->orderBy('shop_name')
            ->get();
    }
}
