<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Requests;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGenericDrugRequest extends FormRequest
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
            'name' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('generic_drugs', 'name')->ignore($this->route('genericDrug')),
            ],
            'status' => ['sometimes', Rule::enum(CatalogStatus::class)],
        ];
    }
}
