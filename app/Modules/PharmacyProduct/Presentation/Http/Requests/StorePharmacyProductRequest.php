<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePharmacyProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::PRODUCT_CREATE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'master_drug_id' => ['nullable', 'uuid', 'exists:master_drugs,id'],
            'local_name' => ['required', 'string', 'max:255'],
            'prescription_required' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
        ];
    }
}
