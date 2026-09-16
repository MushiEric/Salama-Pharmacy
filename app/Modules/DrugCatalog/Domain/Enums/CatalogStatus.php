<?php

namespace App\Modules\DrugCatalog\Domain\Enums;

enum CatalogStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
