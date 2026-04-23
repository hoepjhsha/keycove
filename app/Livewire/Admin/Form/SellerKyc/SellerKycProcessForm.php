<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\SellerKyc;

use App\Enums\KycStatus;
use App\Models\Seller;
use Illuminate\Validation\Rules\Enum;
use Livewire\Form;

class SellerKycProcessForm extends Form
{
    public ?Seller $seller = null;

    public ?int $kyc_status = null;

    public ?string $kyc_rejected_reason = null;

    public function rules(): array
    {
        return [
            'kyc_status'          => ['required', new Enum(KycStatus::class)],
            'kyc_rejected_reason' => ['required_if:kyc_status,'.KycStatus::Rejected->value, 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kyc_rejected_reason.required_if' => 'Please provide a reason for rejecting the KYC.',
        ];
    }

    public function setSeller(Seller $seller): void
    {
        $this->seller = $seller;
        $this->kyc_status = $seller->kyc_status->value;
        $this->kyc_rejected_reason = $seller->kyc_rejected_reason;
    }

    public function update(): bool
    {
        $this->validate();

        if (! $this->seller) {
            return false;
        }

        $this->seller->kyc_status = KycStatus::tryFrom((int) $this->kyc_status);
        $this->seller->kyc_rejected_reason = $this->seller->kyc_status === KycStatus::Rejected ? $this->kyc_rejected_reason : null;

        return $this->seller->save();
    }
}
