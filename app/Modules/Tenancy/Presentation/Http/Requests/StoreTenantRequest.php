<?php

namespace App\Modules\Tenancy\Presentation\Http\Requests;

use App\Modules\Tenancy\Domain\Enums\TenantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_phone' => ['nullable', 'string', 'max:32'],
            'branch_address' => ['nullable', 'string', 'max:255'],
            'package_id' => ['required', 'uuid', 'exists:packages,id'],
            'status' => ['sometimes', Rule::enum(TenantStatus::class)],
        ];
    }
}
