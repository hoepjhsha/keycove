<?php

declare(strict_types=1);

namespace App\Exceptions\Admin;

use Exception;

final class EscrowException extends Exception
{
    public static function invalidStatusForExtension(): self
    {
        return new self('Only escrows with Holding or Frozen status can be extended.');
    }

    public static function invalidStatusForRelease(): self
    {
        return new self('Only escrows with Holding status can be released.');
    }

    public static function maxExtensionLimitReached($maxDate): self
    {
        return new self('This escrow has reached the maximum extension limit. Max extend to: '.$maxDate->format('d/m/Y'));
    }

    public static function extensionExceedsLimit($maxDate): self
    {
        return new self('Extension exceeds maximum allowed limit. Max extend to: '.$maxDate->format('d/m/Y'));
    }

    public static function statusChangedDuringProcess(): self
    {
        return new self('Escrow status has changed during the process. Please try again.');
    }

    public static function invalidStatusForFreeze(): self
    {
        return new self('Only escrows with Holding status can be frozen.');
    }

    public static function invalidStatusForUnfreeze(): self
    {
        return new self('Only escrows with Frozen status can be unfrozen.');
    }

    public static function statusChangedDuringFreeze(): self
    {
        return new self('Escrow status is no longer Holding. Freeze cancelled.');
    }

    public static function statusChangedDuringUnfreeze(): self
    {
        return new self('Escrow status is no longer Frozen. Unfreeze cancelled.');
    }
}
