<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Requests;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGenericDrugRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:generic_drugs,name'],
            'status' => ['sometimes', Rule::enum(CatalogStatus::class)],
        ];
    }
}
