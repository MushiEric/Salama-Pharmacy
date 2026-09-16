<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Requests;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterDrugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformSuperadmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'generic_drug_id' => ['sometimes', 'uuid', 'exists:generic_drugs,id'],
            'brand_name' => ['sometimes', 'string', 'max:255'],
            'dosage_form' => ['sometimes', 'nullable', 'string', 'max:255'],
            'strength' => ['sometimes', 'nullable', 'string', 'max:255'],
            'manufacturer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'barcode' => [
                'sometimes', 'nullable', 'string', 'max:255',
                Rule::unique('master_drugs', 'barcode')->ignore($this->route('masterDrug')),
            ],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(CatalogStatus::class)],
        ];
    }
}
