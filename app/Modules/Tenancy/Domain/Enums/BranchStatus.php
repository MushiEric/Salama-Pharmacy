<?php

namespace App\Modules\Tenancy\Domain\Enums;

enum BranchStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
