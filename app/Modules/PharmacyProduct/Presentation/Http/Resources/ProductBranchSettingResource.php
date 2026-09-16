<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Resources;

use App\Modules\PharmacyProduct\Models\ProductBranchSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductBranchSetting
 */
class ProductBranchSettingResource extends JsonResource
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
            'selling_price' => $this->selling_price,
            'reorder_level_base' => $this->reorder_level_base,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
