<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\SellerKyc;

use App\Enums\KycStatus;
use App\Livewire\Admin\Form\SellerKyc\SellerKycProcessForm;
use App\Models\Seller;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Seller Verifications (KYC)')]
class SellerKycIndex extends Component
{
    public bool $showProcessModal = false;

    public bool $showViewModal = false;

    public ?array $viewData = null;

    public SellerKycProcessForm $processForm;

    public function processSellerKycSubmit(): void
    {
        $result = $this->processForm->update();
        if ($result) {
            sweetalert()->title('Success!')->showConfirmButton(false)->success('KYC Status updated successfully');
            $this->dispatch('pg:eventRefresh-sellerKycTable');
        } else {
            sweetalert()->error('Failed to update KYC Status');
        }
        $this->showProcessModal = false;
    }

    public function render()
    {
        return view('pages.admin.seller-kyc.index')->layout('components.layouts.dashboard');
    }

    #[On('viewSellerKyc')]
    public function viewSellerKyc($rowId): void
    {
        $seller = Seller::with('user')->find($rowId);

        if ($seller) {
            $colorClass = match ($seller->kyc_status) {
                KycStatus::Approved => 'bg-green-500/10 text-green-500',
                KycStatus::Pending  => 'bg-yellow-500/10 text-yellow-500',
                KycStatus::Rejected => 'bg-red-500/10 text-red-500',
                default             => 'bg-primary-500/10 text-primary-500',
            };

            $statusLabel = '<span class="'.$colorClass.' text-[11px] font-medium mr-1 px-2.5 py-0.5 rounded-full">'.$seller->kyc_status->label().'</span>';

            $getImgUrl = function ($path, $type, $sellerId) {
                if (! $path) {
                    return null;
                }

                return route('admin.seller_verifications.image', ['seller' => $sellerId, 'type' => $type]);
            };

            $this->viewData = [
                'id'                  => $seller->id,
                'user_name'           => $seller->user?->username ?? 'Unknown',
                'user_email'          => $seller->user?->email ?? 'Unknown',
                'shop_name'           => $seller->shop_name,
                'cccd_number'         => $seller->cccd_number,
                'cccd_front_image'    => $getImgUrl($seller->cccd_front_image, 'front', $seller->id),
                'cccd_back_image'     => $getImgUrl($seller->cccd_back_image, 'back', $seller->id),
                'status_label'        => $statusLabel,
                'kyc_rejected_reason' => $seller->kyc_rejected_reason,
                'created_at'          => $seller->created_at ? $seller->created_at->format('d/m/Y H:i:s') : 'N/A',
                'updated_at'          => $seller->updated_at ? $seller->updated_at->format('d/m/Y H:i:s') : 'N/A',
            ];

            $this->showViewModal = true;
        }
    }

    #[On('processSellerKyc')]
    public function processSellerKyc($rowId): void
    {
        $seller = Seller::with('user')->find($rowId);

        if ($seller && $seller->kyc_status === KycStatus::Pending) {
            $this->processForm->setSeller($seller);
            $this->showProcessModal = true;
        } else {
            sweetalert()->error('Seller KYC is already processed or not found.');
        }
    }
}
