<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Resources;

use App\Modules\PharmacyProduct\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductUnit
 */
class ProductUnitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pharmacy_product_id' => $this->pharmacy_product_id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'multiplier_to_base' => $this->multiplier_to_base,
            'is_base' => $this->is_base,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
