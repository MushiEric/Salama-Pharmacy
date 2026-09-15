<?php

namespace App\Modules\Tenancy\Domain\Enums;

enum TenantStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
