<?php

namespace App\Modules\Inventory\Domain\Enums;

enum ExpiryStatus: string
{
    case Red = 'red';
    case Yellow = 'yellow';
    case Green = 'green';
}
