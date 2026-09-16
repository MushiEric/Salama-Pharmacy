<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Resources;

use App\Modules\DrugCatalog\Models\GenericDrug;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GenericDrug
 */
class GenericDrugResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
