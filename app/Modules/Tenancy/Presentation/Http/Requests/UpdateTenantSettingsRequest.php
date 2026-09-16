<?php

namespace App\Modules\Tenancy\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::SETTINGS_MANAGE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'expiry_red_days' => ['sometimes', 'integer', 'min:0'],
            'expiry_yellow_days' => ['sometimes', 'integer', 'min:0'],
            'block_expired_sales' => ['sometimes', 'boolean'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'timezone' => ['sometimes', 'timezone'],
        ];
    }
}
