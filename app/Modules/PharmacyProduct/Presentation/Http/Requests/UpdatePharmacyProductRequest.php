<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePharmacyProductRequest extends FormRequest
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
            'master_drug_id' => ['sometimes', 'nullable', 'uuid', 'exists:master_drugs,id'],
            'local_name' => ['sometimes', 'string', 'max:255'],
            'prescription_required' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
        ];
    }
}
