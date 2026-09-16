<?php

namespace App\Modules\PharmacyProduct\Domain\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
