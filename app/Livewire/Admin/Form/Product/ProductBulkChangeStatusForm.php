<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Models\Product;
use App\Models\ProductListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductBulkChangeStatusForm extends Form
{
    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

    public function setStatus(array $ids): bool
    {
        $this->validate();

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'bulkChangeStatusForm.status' => __('admin.validation.status_deleted_bulk_action'),
            ]);
        }

        DB::transaction(function () use ($ids): void {
            Product::query()
                ->whereIn('id', $ids)
                ->get()
                ->each(function (Product $product): void {
                    $product->status = $this->status;
                    $product->save();
                });

            ProductListing::query()
                ->whereHas('variant.product', function ($query) use ($ids): void {
                    $query->whereIn('id', $ids);
                })
                ->update([
                    'status' => $this->status === GeneralStatus::Active->value
                        ? ProductListingStatus::Active->value
                        : ProductListingStatus::Draft->value,
                ]);
        });

        return true;
    }
}
