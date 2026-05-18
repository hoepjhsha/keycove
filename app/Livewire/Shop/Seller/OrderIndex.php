<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller;

use App\Enums\ComplaintStatus;
use App\Enums\EscrowStatus;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Đơn hàng của tôi')]
class OrderIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public string $paymentStatus = 'all';

    public string $complaintFilter = 'all';

    public string $timeFilter = 'last_30_days';

    public string $sortBy = 'latest';

    public bool $showDetailModal = false;

    public ?int $viewingOrderItemId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updatedComplaintFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTimeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $user = $this->resolveUser();
        $user->loadMissing('seller.wallet');

        $seller = $this->resolveSeller($user);
        [$startDate, $endDate] = $this->dateRange();

        $baseQuery = $this->baseQuery($seller, $startDate, $endDate);

        return view('pages.shop.seller.orders.index', [
            'user'       => $user,
            'seller'     => $seller,
            'rangeLabel' => $this->rangeLabel($startDate, $endDate),
            'items'      => $this->items($baseQuery),
            'metrics'    => $this->metrics($seller, $startDate, $endDate),
            'detailItem' => $this->detailItem(),
        ])->layout('components.layouts.seller', [
            'title'         => 'Đơn hàng của tôi',
            'user'          => $user,
            'seller'        => $seller,
            'activeSection' => 'orders',
        ]);
    }

    public function openDetailModal(int $orderItemId): void
    {
        $this->viewingOrderItemId = $orderItemId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->viewingOrderItemId = null;
    }

    protected function detailItem(): ?OrderItem
    {
        if ($this->viewingOrderItemId === null) {
            return null;
        }

        $seller = $this->resolveSeller($this->resolveUser());

        return OrderItem::query()
            ->whereKey($this->viewingOrderItemId)
            ->where('seller_id', $seller->id)
            ->with([
                'order.buyer',
                'order.paymentTransactions',
                'listing.variant.product',
                'listing.variant.region',
                'listing.variant.platform',
                'listing.variant.operatingSystem',
                'escrow',
                'complaint.messages.sender',
            ])
            ->first();
    }

    protected function metrics(Seller $seller, CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $totalItems = OrderItem::query()
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->count();

        $totalOrders = OrderItem::query()
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->distinct()
            ->count('order_items.order_id');

        $totalRevenue = (float) OrderItem::query()
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->sum('order_items.seller_amount');

        $totalFee = (float) OrderItem::query()
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->sum('order_items.platform_fee');

        $holdingEscrow = (float) OrderItem::query()
            ->join('escrows', 'escrows.order_item_id', '=', 'order_items.id')
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->where('escrows.status', EscrowStatus::Holding->value)
            ->sum('escrows.amount');

        $openComplaints = OrderItem::query()
            ->join('complaints', 'complaints.order_item_id', '=', 'order_items.id')
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->whereIn('complaints.status', [ComplaintStatus::Open->value, ComplaintStatus::InProcess->value, ComplaintStatus::Escalated->value])
            ->count();

        return [
            'totalItems'     => $totalItems,
            'totalOrders'    => $totalOrders,
            'totalRevenue'   => $totalRevenue,
            'totalFee'       => $totalFee,
            'holdingEscrow'  => $holdingEscrow,
            'openComplaints' => $openComplaints,
        ];
    }

    protected function items(Builder $query): LengthAwarePaginator
    {
        $items = $query->withCount('keys')->paginate(15);

        $items->setCollection($items->getCollection()->transform(function (OrderItem $item): array {
            $complaint = $item->complaint;
            $escrow = $item->escrow;
            $paymentStatus = $item->order?->payment_status;

            return [
                'id'              => $item->id,
                'order_code'      => $item->order?->order_code,
                'order_item_code' => $item->order_item_code,
                'buyer_username'  => $item->order?->buyer?->username,
                'buyer_email'     => $item->order?->buyer?->email,
                'product_name'    => $item->product_name_snapshot,
                'variant_summary' => collect([
                    $item->listing?->variant?->region?->name,
                    $item->listing?->variant?->platform?->name,
                    $item->listing?->variant?->operatingSystem?->name,
                    $item->listing?->variant?->edition,
                ])->filter()->implode(' · '),
                'quantity'         => $item->quantity,
                'subtotal'         => (float) $item->subtotal,
                'platform_fee'     => (float) $item->platform_fee,
                'seller_amount'    => (float) $item->seller_amount,
                'status'           => $item->status,
                'status_label'     => $item->status->label(),
                'payment_status'   => $paymentStatus,
                'payment_label'    => $paymentStatus?->label(),
                'complaint'        => $complaint,
                'complaint_code'   => $complaint?->complaint_code,
                'complaint_status' => $complaint?->status,
                'escrow_status'    => $escrow?->status,
                'escrow_amount'    => $escrow?->amount,
                'created_at'       => $item->created_at,
                'keys_count'       => $item->keys_count,
            ];
        }));

        return $items;
    }

    protected function baseQuery(Seller $seller, CarbonImmutable $startDate, CarbonImmutable $endDate): Builder
    {
        $query = OrderItem::query()
            ->where('order_items.seller_id', $seller->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->with([
                'order.buyer',
                'listing.variant.product',
                'listing.variant.region',
                'listing.variant.platform',
                'listing.variant.operatingSystem',
                'escrow',
                'complaint',
            ]);

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('order_items.order_item_code', 'like', '%'.$search.'%')
                    ->orWhere('order_items.product_name_snapshot', 'like', '%'.$search.'%')
                    ->orWhereHas('order', function (Builder $orderQuery) use ($search): void {
                        $orderQuery
                            ->where('order_code', 'like', '%'.$search.'%')
                            ->orWhereHas('buyer', function (Builder $buyerQuery) use ($search): void {
                                $buyerQuery
                                    ->where('username', 'like', '%'.$search.'%')
                                    ->orWhere('email', 'like', '%'.$search.'%');
                            });
                    });
            });
        }

        if ($this->status !== 'all') {
            $query->where('order_items.status', (int) $this->status);
        }

        if ($this->paymentStatus !== 'all') {
            $query->whereHas('order', function (Builder $orderQuery): void {
                $orderQuery->where('payment_status', (int) $this->paymentStatus);
            });
        }

        if ($this->complaintFilter === 'with') {
            $query->whereHas('complaint');
        } elseif ($this->complaintFilter === 'without') {
            $query->whereDoesntHave('complaint');
        } elseif ($this->complaintFilter === 'open') {
            $query->whereHas('complaint', function (Builder $complaintQuery): void {
                $complaintQuery->whereIn('status', [ComplaintStatus::Open->value, ComplaintStatus::InProcess->value, ComplaintStatus::Escalated->value]);
            });
        }

        return match ($this->sortBy) {
            'oldest'       => $query->oldest('order_items.created_at'),
            'revenue_high' => $query->orderByDesc('order_items.seller_amount')->orderByDesc('order_items.created_at'),
            'revenue_low'  => $query->orderBy('order_items.seller_amount')->orderByDesc('order_items.created_at'),
            default        => $query->latest('order_items.created_at'),
        };
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function dateRange(): array
    {
        $endDate = CarbonImmutable::now()->endOfDay();

        return match ($this->timeFilter) {
            'today'         => [$endDate->startOfDay(), $endDate],
            'last_7_days'   => [$endDate->subDays(6)->startOfDay(), $endDate],
            'month_to_date' => [$endDate->startOfMonth()->startOfDay(), $endDate],
            'year_to_date'  => [$endDate->startOfYear()->startOfDay(), $endDate],
            'all_time'      => [CarbonImmutable::createFromTimestamp(0), $endDate],
            default         => [$endDate->subDays(29)->startOfDay(), $endDate],
        };
    }

    protected function rangeLabel(CarbonImmutable $startDate, CarbonImmutable $endDate): string
    {
        return $startDate->isSameDay($endDate)
            ? $startDate->format('d/m/Y')
            : $startDate->format('d/m/Y').' - '.$endDate->format('d/m/Y');
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
