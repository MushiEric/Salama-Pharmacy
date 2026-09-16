<?php

namespace App\Modules\Procurement\Presentation\Http\Resources;

use App\Modules\Procurement\Models\StockReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockReceipt
 */
class StockReceiptResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'supplier_id' => $this->supplier_id,
            'reference_no' => $this->reference_no,
            'received_by_user_id' => $this->received_by_user_id,
            'received_at' => $this->received_at,
            'status' => $this->status,
            'items' => StockReceiptItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
