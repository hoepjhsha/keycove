<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Table\Escrow;

use App\Enums\AuditEvent;
use App\Enums\EscrowStatus;
use App\Enums\TransactionBalanceType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Exceptions\Admin\EscrowException;
use App\Models\AuditLog;
use App\Models\Escrow;
use App\Models\Wallet;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class EscrowTable extends PowerGridComponent
{
    public string $tableName = 'escrowTable';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Escrow::query()
            ->with(['orderItem.order.buyer', 'seller'])
            ->join('order_items', 'escrows.order_item_id', '=', 'order_items.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('users', 'orders.buyer_id', '=', 'users.id')
            ->leftJoin('sellers', 'escrows.seller_id', '=', 'sellers.id')
            ->select('escrows.*');
    }

    public function relationSearch(): array
    {
        return [
            'orders'  => ['order_code'],
            'users'   => ['username', 'email'],
            'sellers' => ['shop_name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('order_code', fn (Escrow $model) => $model->orderItem?->order?->order_code ?? '-')
            ->add('buyer', fn (Escrow $model) => $model->orderItem?->order?->buyer?->username ?? '-')
            ->add('seller', fn (Escrow $model) => $model?->seller?->shop_name ?? 'Shop Admin')
            ->add('amount_formatted', fn (Escrow $model) => number_format((float) $model->amount, 2).' VND')
            ->add('release_date_formatted', fn (Escrow $model) => Carbon::parse($model->release_date)->format('d/m/Y H:i:s'))
            ->add('status_label', function (Escrow $model) {
                $status = $model->status;
                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;

                $colorClass = match ($status) {
                    EscrowStatus::Holding  => 'bg-blue-500/10 text-blue-500',
                    EscrowStatus::Released => 'bg-green-500/10 text-green-500',
                    EscrowStatus::Refunded => 'bg-red-500/10 text-red-500',
                    EscrowStatus::Frozen   => 'bg-purple-500/10 text-purple-500',
                    default                => 'bg-gray-500/10 text-gray-500',
                };

                return '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$labelText.'</span>';
            })
            ->add('created_at_formatted', fn (Escrow $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (Escrow $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'id')
                ->index(),
            Column::make('Order Code', 'order_code', 'order.order_code'),
            Column::make('Buyer', 'buyer', 'buyer.id'),
            Column::make('Seller', 'seller', 'seller.id'),
            Column::make('Amount', 'amount_formatted', 'amount')
                ->sortable()
                ->bodyAttribute('text-right'),

            Column::make('Release date', 'release_date_formatted', 'release_date')
                ->sortable(),

            Column::make('Status', 'status_label', 'status')
                ->sortable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Updated at', 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('order_code', 'orders.order_code')->operators(['contains']),

            Filter::inputText('buyer', 'users.username')->operators(['contains']),

            Filter::inputText('seller', 'sellers.shop_name')->operators(['contains']),

            Filter::number('amount')
                ->thousands('.')
                ->decimal(','),

            Filter::multiSelect('status', 'escrows.status')
                ->dataSource(collect(EscrowStatus::cases())->map(fn ($status) => [
                    'id'   => $status->value,
                    'name' => method_exists($status, 'label') ? $status->label() : $status->name,
                ]))
                ->optionValue('id')
                ->optionLabel('name'),

            // TODO: Datepicker filters disabled - need to fix date range handling
            // Filter::datepicker('release_date_formatted', 'escrows.release_date'),
            // Filter::datepicker('created_at_formatted', 'escrows.created_at'),
            // Filter::datepicker('updated_at_formatted', 'escrows.updated_at'),
        ];
    }

    public function actions(Escrow $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa-regular fa-eye"></i>')
                ->id()
                ->class('text-indigo-600 hover:text-indigo-900 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'View Details',
                ])
                ->dispatch('viewEscrowDetail', ['rowId' => $row->id]),

            Button::add('freeze')
                ->slot('<i class="fa-solid fa-lock"></i>')
                ->id()
                ->class('text-purple-600 hover:text-purple-800 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'Freeze Escrow',
                ])
                ->dispatch('freezeEscrow', ['rowId' => $row->id]),

            Button::add('extend')
                ->slot('<i class="fa-solid fa-hourglass-end"></i>')
                ->id()
                ->class('text-yellow-600 hover:text-yellow-800 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'Extend Holding Time',
                ])
                ->dispatch('extendHolding', ['rowId' => $row->id]),

            Button::add('release')
                ->slot('<i class="fa-solid fa-unlock"></i>')
                ->id()
                ->class('text-green-600 hover:text-green-800 px-1 py-1 transition-all hover:scale-110')
                ->attributes([
                    'x-tooltip' => 'Release Escrow',
                ])
                ->dispatch('releaseEscrow', ['rowId' => $row->id]),
        ];
    }

    public function actionRules(Escrow $row): array
    {
        return [
            Rule::button('freeze')
                ->when(fn (Escrow $model) => $model->status !== EscrowStatus::Holding)
                ->hide(),

            Rule::button('extend')
                ->when(fn (Escrow $model) => $model->status !== EscrowStatus::Holding && $model->status !== EscrowStatus::Frozen)
                ->hide(),

            Rule::button('release')
                ->when(fn (Escrow $model) => $model->status !== EscrowStatus::Holding)
                ->hide(),
        ];
    }

    #[On('releaseEscrow')]
    public function releaseEscrow($rowId): void
    {
        $escrow = Escrow::findOrFail($rowId);

        if ($escrow->status !== EscrowStatus::Holding) {
            $this->dispatch('swal:error', [
                'message' => EscrowException::invalidStatusForRelease()->getMessage(),
            ]);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title'  => 'Release Escrow?',
            'text'   => 'Are you sure you want to release this escrow amount to the seller? This action cannot be undone.',
            'method' => 'performReleaseEscrow',
            'id'     => $rowId,
        ]);
    }

    #[On('performReleaseEscrow')]
    public function performReleaseEscrow($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $escrow = Escrow::lockForUpdate()->findOrFail($id);

                if ($escrow->status !== EscrowStatus::Holding) {
                    throw EscrowException::statusChangedDuringProcess();
                }

                $wallet = Wallet::firstOrCreate(
                    ['seller_id' => $escrow->seller_id],
                    ['type' => WalletType::Seller, 'code' => Str::upper(Str::random(12)), 'balance' => 0, 'holding' => 0]
                );

                $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

                $amount = (float) $escrow->amount;

                $oldValues = [
                    'status'     => $escrow->status->name,
                    'updated_at' => $escrow->updated_at->toDateTimeString(),
                ];

                $escrow->status = EscrowStatus::Released;
                $escrow->save();

                $wallet->forceFill([
                    'holding' => round((float) $wallet->holding - $amount, 2),
                    'balance' => round((float) $wallet->balance + $amount, 2),
                ])->save();

                $wallet->transactions()->create([
                    'order_id'     => $escrow->orderItem?->order_id,
                    'source_type'  => Escrow::class,
                    'source_id'    => $escrow->id,
                    'type'         => TransactionType::EscrowRelease,
                    'balance_type' => TransactionBalanceType::Available,
                    'payment_info' => [
                        'escrow_id' => $escrow->id,
                        'source'    => 'admin_release',
                    ],
                    'amount'   => $amount,
                    'status'   => TransactionStatus::Completed,
                    'metadata' => [
                        'escrow_id' => $escrow->id,
                    ],
                ]);

                $newValues = [
                    'status'     => $escrow->status->name,
                    'updated_at' => $escrow->updated_at->toDateTimeString(),
                ];

                AuditLog::create([
                    'user_id'        => Auth::id(),
                    'auditable_type' => Escrow::class,
                    'auditable_id'   => $escrow->id,
                    'event'          => AuditEvent::EscrowReleased,
                    'old_values'     => $oldValues,
                    'new_values'     => $newValues,
                    'ip_address'     => request()->ip(),
                    'user_agent'     => request()->userAgent(),
                    'created_at'     => now(),
                ]);
            });

            $this->dispatch('swal:success', [
                'message' => 'Escrow released successfully.',
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:error', [
                'message' => 'Failed to release escrow: '.$e->getMessage(),
            ]);
        }
    }

    #[On('freezeEscrow')]
    public function freezeEscrow($rowId): void
    {
        $escrow = Escrow::findOrFail($rowId);

        if ($escrow->status !== EscrowStatus::Holding) {
            $this->dispatch('swal:error', [
                'message' => EscrowException::invalidStatusForFreeze()->getMessage(),
            ]);

            return;
        }

        $this->dispatch('swal:confirm', [
            'title'  => 'Freeze Escrow?',
            'text'   => 'Are you sure you want to freeze this escrow? The holding timer will be paused. This action can be undone by extending the holding time.',
            'method' => 'performFreezeEscrow',
            'id'     => $rowId,
        ]);
    }

    #[On('performFreezeEscrow')]
    public function performFreezeEscrow($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $escrow = Escrow::lockForUpdate()->findOrFail($id);

                if ($escrow->status !== EscrowStatus::Holding) {
                    throw EscrowException::statusChangedDuringFreeze();
                }

                $oldValues = [
                    'status'     => $escrow->status->name,
                    'updated_at' => $escrow->updated_at->toDateTimeString(),
                ];

                $escrow->status = EscrowStatus::Frozen;
                $escrow->save();

                $newValues = [
                    'status'     => $escrow->status->name,
                    'updated_at' => $escrow->updated_at->toDateTimeString(),
                ];

                AuditLog::create([
                    'user_id'        => Auth::id(),
                    'auditable_type' => Escrow::class,
                    'auditable_id'   => $escrow->id,
                    'event'          => AuditEvent::EscrowFrozen,
                    'old_values'     => $oldValues,
                    'new_values'     => $newValues,
                    'ip_address'     => request()->ip(),
                    'user_agent'     => request()->userAgent(),
                    'created_at'     => now(),
                ]);
            });

            $this->dispatch('swal:success', [
                'message' => 'Escrow frozen successfully.',
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal:error', [
                'message' => 'Failed to freeze escrow: '.$e->getMessage(),
            ]);
        }
    }

    #[On('extendHolding')]
    public function extendHolding($rowId): void
    {
        $this->dispatch('openExtendModal', id: $rowId)->to('App\Livewire\Admin\Action\Escrow\EscrowIndex');
    }
}
