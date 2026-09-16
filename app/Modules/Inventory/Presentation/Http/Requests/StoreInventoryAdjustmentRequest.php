<?php

namespace App\Modules\Inventory\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Inventory\Domain\Enums\AdjustmentReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::INVENTORY_ADJUST) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'uuid'],
            'quantity_delta_base' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', Rule::enum(AdjustmentReason::class)],
            'notes' => ['required_if:reason,other', 'nullable', 'string', 'max:1000'],
        ];
    }
}
