<?php

namespace App\Modules\Identity\Domain\Enums;

enum SystemRole: string
{
    case PlatformSuperadmin = 'platform_superadmin';
    case PharmacyAdmin = 'pharmacy_admin';

    public function displayName(): string
    {
        return match ($this) {
            self::PlatformSuperadmin => 'Platform Superadmin',
            self::PharmacyAdmin => 'Pharmacy Admin',
        };
    }
}
