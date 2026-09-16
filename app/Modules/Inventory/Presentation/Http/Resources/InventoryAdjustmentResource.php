<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use App\Modules\Inventory\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InventoryAdjustment
 */
class InventoryAdjustmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'pharmacy_product_id' => $this->pharmacy_product_id,
            'batch_id' => $this->batch_id,
            'quantity_delta_base' => $this->quantity_delta_base,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'created_by_user_id' => $this->created_by_user_id,
            'created_at' => $this->created_at,
        ];
    }
}
