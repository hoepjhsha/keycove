<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Escrow;

use App\Constants\Admin\EscrowConstant;
use App\Enums\AuditEvent;
use App\Enums\EscrowStatus;
use App\Exceptions\Admin\EscrowException;
use App\Models\AuditLog;
use App\Models\Escrow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EscrowExtendHoldingForm extends Form
{
    #[Validate(['required', 'regex:/^(\d+)([smhd])$/'])]
    public string $duration = '';

    public function submit(int $extendEscrowId): bool
    {
        $this->validate();

        preg_match('/^(\d+)([smhd])$/', $this->duration, $matches);
        $value = (int) $matches[1];
        $unit = $matches[2];

        return DB::transaction(function () use ($extendEscrowId, $value, $unit) {
            $escrow = Escrow::lockForUpdate()->findOrFail($extendEscrowId);

            if ($escrow->status !== EscrowStatus::Holding && $escrow->status !== EscrowStatus::Frozen) {
                throw EscrowException::invalidStatusForExtension();
            }

            $newReleaseDate = match ($unit) {
                's'     => $escrow->release_date->copy()->addSeconds($value),
                'm'     => $escrow->release_date->copy()->addMinutes($value),
                'h'     => $escrow->release_date->copy()->addHours($value),
                'd'     => $escrow->release_date->copy()->addDays($value),
                default => $escrow->release_date,
            };

            $maxAllowedDate = $escrow->created_at->addDays(EscrowConstant::MAX_EXTEND_DAYS_FROM_CREATED);

            if ($newReleaseDate->greaterThan($maxAllowedDate)) {
                throw EscrowException::maxExtensionLimitReached($maxAllowedDate);
            }

            $oldValues = [
                'release_date' => $escrow->release_date->toDateTimeString(),
                'updated_at'   => $escrow->updated_at->toDateTimeString(),
            ];

            $escrow->release_date = $newReleaseDate;
            $escrow->save();

            AuditLog::create([
                'user_id'        => Auth::id(),
                'auditable_type' => Escrow::class,
                'auditable_id'   => $escrow->id,
                'event'          => AuditEvent::EscrowExtended,
                'old_values'     => array_merge($oldValues, ['input_duration' => $this->duration]),
                'new_values'     => [
                    'release_date' => $escrow->release_date->toDateTimeString(),
                    'updated_at'   => $escrow->updated_at->toDateTimeString(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return true;
        });
    }
}
