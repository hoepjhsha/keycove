<?php

declare(strict_types=1);

namespace App\Livewire\Shop\Seller\Table;

use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Enums\UserRole;
use App\Models\ProductListing;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class SellerListingsTable extends PowerGridComponent
{
    public string $tableName = 'sellerListingsTable';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSoftDeletes()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return ProductListing::query()
            ->select('product_listings.*')
            ->withTrashed()
            ->where('seller_id', $this->sellerId())
            ->with([
                'variant.product.submittedBySeller.user',
                'variant.region',
                'variant.platform',
                'variant.operatingSystem',
            ])
            ->withCount([
                'keys as available_keys_count' => function (Builder $query): void {
                    $query->where('status', ProductKeyStatus::Available->value);
                },
            ]);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('listing_name', function (ProductListing $listing): string {
                return $listing->display_name ?: ($listing->variant?->product?->name ?? 'Untitled listing');
            })
            ->add('product_name', function (ProductListing $listing): string {
                return $listing->variant?->product?->name ?? 'Unknown product';
            })
            ->add('variant_name', function (ProductListing $listing): string {
                return collect([
                    $listing->variant?->region?->name,
                    $listing->variant?->platform?->name,
                    $listing->variant?->operatingSystem?->name,
                    $listing->variant?->edition,
                ])->filter()->implode(' · ');
            })
            ->add('source_label', function (ProductListing $listing): string {
                return $listing->variant?->product?->submitted_by_seller_id === $this->sellerId()
                    ? 'My Product'
                    : 'Admin Catalog';
            })
            ->add('price_formatted', fn (ProductListing $listing): string => number_format((float) $listing->price, 0, ',', '.').' VND')
            ->add('status_label', fn (ProductListing $listing): string => $this->statusLabel($listing->status))
            ->add('available_keys_count')
            ->add('created_at_formatted', fn (ProductListing $listing): string => $listing->created_at?->format('d/m/Y H:i') ?? '--')
            ->add('updated_at_formatted', fn (ProductListing $listing): string => $listing->updated_at?->format('d/m/Y H:i') ?? '--');
    }

    public function columns(): array
    {
        return [
            Column::make('Listing', 'listing_name')
                ->sortable()
                ->searchable(),

            Column::make('Product', 'product_name')
                ->sortable()
                ->searchable(),

            Column::make('Variant', 'variant_name')
                ->searchable(),

            Column::make('Source', 'source_label')
                ->sortable()
                ->searchable(),

            Column::make('Price', 'price_formatted', 'price')
                ->sortable(),

            Column::make('Status', 'status_label', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Available Keys', 'available_keys_count')
                ->sortable(),

            Column::make('Updated', 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('listing_name')->operators(['contains']),
            Filter::inputText('product_name')->operators(['contains']),
            Filter::multiSelect('status', 'status')
                ->dataSource(collect(ProductListingStatus::cases())->map(fn (ProductListingStatus $status): array => [
                    'id'   => $status->value,
                    'name' => $status->label(),
                ]))
                ->optionValue('id')
                ->optionLabel('name'),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('create')
                ->slot('Create Listing')
                ->class('inline-flex items-center rounded-2xl bg-black px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white')
                ->dispatch('openCreateListingModal', []),
        ];
    }

    public function actions(ProductListing $row): array
    {
        $deleteClass = $row->status === ProductListingStatus::Deleted ? 'hidden' : '';

        return [
            Button::add('keys')
                ->slot('<i class="fa-solid fa-key"></i>')
                ->id()
                ->class('px-1 py-1 text-indigo-600 transition-all hover:scale-110 hover:text-indigo-900')
                ->attributes([
                    'x-tooltip' => 'Manage Keys',
                ])
                ->dispatch('openKeysModal', ['listingId' => $row->id]),

            Button::add('toggle-status')
                ->slot($row->status === ProductListingStatus::Active
                    ? '<i class="fa-solid fa-circle-xmark text-red-400 hover:text-red-700"></i>'
                    : '<i class="fa-solid fa-circle-check text-green-500 hover:text-green-800"></i>')
                ->id()
                ->class('px-1 py-1 text-lg transition-all hover:scale-110 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => $row->status === ProductListingStatus::Active ? 'Deactivate' : 'Activate',
                ])
                ->dispatch('toggleListingStatus', ['rowId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->id()
                ->class('px-1 py-1 text-blue-600 transition-all hover:scale-110 hover:text-blue-800 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => 'Edit Listing',
                ])
                ->dispatch('editListing', ['listingId' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash-can"></i>')
                ->id()
                ->class('px-1 py-1 text-red-500 transition-all hover:scale-110 hover:text-red-700 '.$deleteClass)
                ->attributes([
                    'x-tooltip' => 'Delete',
                ])
                ->dispatch('deleteListing', ['rowId' => $row->id]),

            Button::add('restore')
                ->slot('<i class="fa-solid fa-rotate-left"></i>')
                ->id()
                ->class('px-1 py-1 text-yellow-500 transition-all hover:scale-110 hover:text-yellow-700 '.($row->status === ProductListingStatus::Deleted ? '' : 'hidden'))
                ->attributes([
                    'x-tooltip' => 'Restore',
                ])
                ->dispatch('restoreListing', ['rowId' => $row->id]),
        ];
    }

    #[On('toggleListingStatus')]
    public function toggleListingStatus(int $rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Change Status?',
            'text'   => 'Are you sure you want to change this listing status?',
            'method' => 'performToggleListingStatus',
            'id'     => $rowId,
        ]);
    }

    #[On('performToggleListingStatus')]
    public function performToggleListingStatus(int $id): void
    {
        $listing = $this->resolveOwnedListing($id);

        $listing->status = match ($listing->status) {
            ProductListingStatus::Active  => ProductListingStatus::Hidden,
            ProductListingStatus::Hidden  => ProductListingStatus::Draft,
            ProductListingStatus::Draft   => ProductListingStatus::Active,
            ProductListingStatus::Pending => ProductListingStatus::Active,
            default                       => ProductListingStatus::Draft,
        };

        $listing->save();

        $this->dispatch('swal:success', ['message' => 'Listing status updated.']);
    }

    #[On('deleteListing')]
    public function deleteListing(int $rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Delete Listing?',
            'text'   => 'Are you sure you want to delete this listing?',
            'method' => 'performDeleteListing',
            'id'     => $rowId,
        ]);
    }

    #[On('performDeleteListing')]
    public function performDeleteListing(int $id): void
    {
        try {
            $listing = $this->resolveOwnedListing($id);

            if ($listing->keys()->where('status', ProductKeyStatus::Sold->value)->exists()) {
                throw new \RuntimeException('Cannot delete listing with sold keys.');
            }

            $listing->status = ProductListingStatus::Deleted;
            $listing->save();
            $listing->delete();

            $this->dispatch('swal:success', ['message' => 'Listing deleted successfully.']);
        } catch (\Throwable $e) {
            $this->dispatch('swal:error', ['message' => $e->getMessage()]);
        }
    }

    #[On('restoreListing')]
    public function restoreListing(int $rowId): void
    {
        $this->dispatch('swal:confirm', [
            'title'  => 'Restore Listing?',
            'text'   => 'Are you sure you want to restore this listing?',
            'method' => 'performRestoreListing',
            'id'     => $rowId,
        ]);
    }

    #[On('performRestoreListing')]
    public function performRestoreListing(int $id): void
    {
        $listing = $this->resolveOwnedListing($id, true);
        $listing->restore();
        $listing->status = ProductListingStatus::Draft;
        $listing->save();

        $this->dispatch('swal:success', ['message' => 'Listing restored successfully.']);
    }

    protected function statusLabel(ProductListingStatus $status): string
    {
        $colorClass = match ($status) {
            ProductListingStatus::Draft    => 'bg-gray-500/10 text-gray-500',
            ProductListingStatus::Pending  => 'bg-blue-500/10 text-blue-500',
            ProductListingStatus::Active   => 'bg-green-500/10 text-green-500',
            ProductListingStatus::Hidden   => 'bg-yellow-500/10 text-yellow-500',
            ProductListingStatus::Rejected => 'bg-red-500/10 text-red-500',
            ProductListingStatus::Closed   => 'bg-orange-500/10 text-orange-500',
            ProductListingStatus::Deleted  => 'bg-red-500/10 text-red-500',
            default                        => 'bg-slate-500/10 text-slate-500',
        };

        return '<span class="'.$colorClass.' rounded-full px-2.5 py-0.5 text-[11px] font-medium">'.$status->label().'</span>';
    }

    protected function sellerId(): int
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);
        $user->loadMissing('seller');
        abort_unless($user->seller instanceof Seller, 403);
        abort_unless($user->role === UserRole::Seller, 403);

        return $user->seller->id;
    }

    protected function resolveOwnedListing(int $id, bool $withTrashed = false): ProductListing
    {
        $query = ProductListing::query()->where('seller_id', $this->sellerId());

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }
}
