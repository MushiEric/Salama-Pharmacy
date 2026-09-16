<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::PRODUCT_UPDATE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:32'],
            'multiplier_to_base' => ['required', 'numeric', 'gt:0'],
            'is_base' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
        ];
    }
}
