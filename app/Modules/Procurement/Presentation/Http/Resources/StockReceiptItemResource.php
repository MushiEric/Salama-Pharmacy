<?php

namespace App\Modules\Procurement\Presentation\Http\Resources;

use App\Modules\Procurement\Models\StockReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockReceiptItem
 */
class StockReceiptItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pharmacy_product_id' => $this->pharmacy_product_id,
            'product_unit_id' => $this->product_unit_id,
            'quantity_in_unit' => $this->quantity_in_unit,
            'quantity_base' => $this->quantity_base,
            'batch_number' => $this->batch_number,
            'manufactured_at' => $this->manufactured_at,
            'expires_at' => $this->expires_at,
            'unit_cost_base' => $this->unit_cost_base,
            'inventory_batch_id' => $this->inventory_batch_id,
        ];
    }
}
