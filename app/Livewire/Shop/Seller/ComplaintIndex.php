<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\ComplaintStatus;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Seller Complaints')]
class ComplaintIndex extends Component
{
    public function render(): View
    {
        $user = $this->resolveUser();
        $seller = $this->resolveSeller($user);

        $complaints = Complaint::query()
            ->whereHas('orderItem', function (Builder $query) use ($seller): void {
                $query->where('seller_id', $seller->id);
            })
            ->with([
                'orderItem.order.buyer',
                'orderItem.listing.variant.product',
                'orderItem.listing.variant.region',
                'orderItem.listing.variant.platform',
                'orderItem.listing.variant.operatingSystem',
                'messages',
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('pages.shop.seller.complaints.index', [
            'user'          => $user,
            'seller'        => $seller,
            'complaints'    => $complaints,
            'openCount'     => $complaints->where('status', ComplaintStatus::Open)->count(),
            'activeCount'   => $complaints->whereIn('status', [ComplaintStatus::Open, ComplaintStatus::InProcess, ComplaintStatus::Escalated])->count(),
            'resolvedCount' => $complaints->whereIn('status', [ComplaintStatus::ApprovedRefund, ComplaintStatus::RejectedRelease])->count(),
        ])->layout('components.layouts.seller', [
            'title'         => 'Seller Complaints',
            'user'          => $user,
            'seller'        => $seller,
            'activeSection' => 'complaints',
        ]);
    }

    protected function resolveUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function resolveSeller(User $user): Seller
    {
        $seller = $user->seller;

        abort_unless($seller instanceof Seller, 403);
        abort_unless($user->role === UserRole::Seller && $seller->kyc_status === KycStatus::Approved, 403);

        return $seller;
    }
}
