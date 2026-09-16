<?php

namespace App\Modules\Tenancy\Presentation\Http\Resources;

use App\Modules\Tenancy\Domain\ValueObjects\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantSettings
 */
class TenantSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'expiry_red_days' => $this->expiryRedDays,
            'expiry_yellow_days' => $this->expiryYellowDays,
            'block_expired_sales' => $this->blockExpiredSales,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
        ];
    }
}
