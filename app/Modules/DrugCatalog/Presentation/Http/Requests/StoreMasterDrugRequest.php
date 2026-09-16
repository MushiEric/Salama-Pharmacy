<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Requests;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterDrugRequest extends FormRequest
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
            'generic_drug_id' => ['required', 'uuid', 'exists:generic_drugs,id'],
            'brand_name' => ['required', 'string', 'max:255'],
            'dosage_form' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:master_drugs,barcode'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(CatalogStatus::class)],
        ];
    }
}
