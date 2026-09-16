<?php

namespace App\Modules\Inventory\Domain\Enums;

enum AdjustmentReason: string
{
    case Damaged = 'damaged';
    case Expired = 'expired';
    case Lost = 'lost';
    case Correction = 'correction';
    case Other = 'other';
}
