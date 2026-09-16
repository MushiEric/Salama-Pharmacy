<?php

namespace App\Modules\Supplier\Domain\Enums;

enum SupplierStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
