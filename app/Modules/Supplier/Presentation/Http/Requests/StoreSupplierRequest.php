<?php

namespace App\Modules\Supplier\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Supplier\Domain\Enums\SupplierStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::SUPPLIER_MANAGE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::enum(SupplierStatus::class)],
        ];
    }
}
