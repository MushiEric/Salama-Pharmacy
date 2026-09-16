<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use App\Modules\Inventory\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InventoryMovement
 */
class InventoryMovementResource extends JsonResource
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
            'type' => $this->type,
            'quantity_delta_base' => $this->quantity_delta_base,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'reason' => $this->reason,
            'actor_user_id' => $this->actor_user_id,
            'occurred_at' => $this->occurred_at,
            'metadata' => $this->metadata,
        ];
    }
}
