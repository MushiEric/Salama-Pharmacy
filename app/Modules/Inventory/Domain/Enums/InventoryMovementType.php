<?php

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryMovementType: string
{
    case Receipt = 'receipt';
    case Sale = 'sale';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case Reversal = 'reversal';
}
