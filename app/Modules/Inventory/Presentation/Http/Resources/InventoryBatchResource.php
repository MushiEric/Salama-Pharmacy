<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use App\Modules\Inventory\Domain\Policies\BatchSaleEligibilityPolicy;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InventoryBatch
 */
class InventoryBatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $settings = app(TenantContext::class)->tenant()->settings();
        $policy = app(BatchSaleEligibilityPolicy::class);

        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'pharmacy_product_id' => $this->pharmacy_product_id,
            'supplier_id' => $this->supplier_id,
            'batch_number' => $this->batch_number,
            'received_at' => $this->received_at,
            'manufactured_at' => $this->manufactured_at,
            'expires_at' => $this->expires_at,
            'initial_quantity_base' => $this->initial_quantity_base,
            'available_quantity_base' => $this->available_quantity_base,
            'unit_cost_base' => $this->unit_cost_base,
            'status' => $this->status,
            'expiry_status' => $policy->expiryStatus($this->expires_at, $settings),
            'eligibility' => $policy->eligibility($this->expires_at, $settings),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
