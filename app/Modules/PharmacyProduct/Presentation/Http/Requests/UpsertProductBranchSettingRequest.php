<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;

class UpsertProductBranchSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::PRODUCT_PRICE_UPDATE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'uuid'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level_base' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
