<?php

namespace App\Modules\Inventory\Domain\Enums;

enum BatchStatus: string
{
    case Active = 'active';
    case Depleted = 'depleted';
}
