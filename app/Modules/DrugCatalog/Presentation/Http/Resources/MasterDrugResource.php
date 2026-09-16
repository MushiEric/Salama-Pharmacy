<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Resources;

use App\Modules\DrugCatalog\Models\MasterDrug;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MasterDrug
 */
class MasterDrugResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'generic_drug_id' => $this->generic_drug_id,
            'generic_drug_name' => $this->whenLoaded('genericDrug', fn () => $this->genericDrug->name),
            'brand_name' => $this->brand_name,
            'dosage_form' => $this->dosage_form,
            'strength' => $this->strength,
            'manufacturer' => $this->manufacturer,
            'barcode' => $this->barcode,
            'category' => $this->category,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
