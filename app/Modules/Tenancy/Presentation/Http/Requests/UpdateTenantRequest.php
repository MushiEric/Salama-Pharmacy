<?php

namespace App\Modules\Tenancy\Presentation\Http\Requests;

use App\Modules\Tenancy\Domain\Enums\TenantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(TenantStatus::class)],
            'settings' => ['sometimes', 'array'],
        ];
    }
}
