<?php

namespace App\Modules\Subscription\Domain\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function isReadOnly(): bool
    {
        return match ($this) {
            self::Expired, self::Suspended, self::Cancelled => true,
            self::Trial, self::Active => false,
        };
    }
}
