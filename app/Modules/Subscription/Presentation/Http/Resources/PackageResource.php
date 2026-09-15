<?php

namespace App\Modules\Subscription\Presentation\Http\Resources;

use App\Modules\Subscription\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Package
 */
class PackageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'max_branches' => $this->max_branches,
            'max_users' => $this->max_users,
            'max_devices' => $this->max_devices,
            'max_stock_items' => $this->max_stock_items,
            'max_transactions_per_period' => $this->max_transactions_per_period,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
