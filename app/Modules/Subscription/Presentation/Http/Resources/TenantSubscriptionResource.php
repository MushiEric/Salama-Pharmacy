<?php

namespace App\Modules\Subscription\Presentation\Http\Resources;

use App\Modules\Subscription\Models\TenantSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantSubscription
 */
class TenantSubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'status' => $this->status,
            'is_read_only' => $this->isReadOnly(),
            'trial_ends_at' => $this->trial_ends_at,
            'current_period_ends_at' => $this->current_period_ends_at,
            'cancelled_at' => $this->cancelled_at,
            'package' => new PackageResource($this->whenLoaded('package')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
