<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Resources;

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PharmacyProduct
 */
class PharmacyProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'master_drug_id' => $this->master_drug_id,
            'local_name' => $this->local_name,
            'prescription_required' => $this->prescription_required,
            'status' => $this->status,
            'units' => ProductUnitResource::collection($this->whenLoaded('units')),
            'branch_settings' => ProductBranchSettingResource::collection($this->whenLoaded('branchSettings')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
