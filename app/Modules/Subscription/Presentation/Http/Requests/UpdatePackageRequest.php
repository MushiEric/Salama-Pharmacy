<?php

namespace App\Modules\Subscription\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
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
            'slug' => [
                'sometimes', 'string', 'max:255', 'alpha_dash',
                Rule::unique('packages', 'slug')->ignore($this->route('package')),
            ],
            'max_branches' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_users' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_devices' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_stock_items' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_transactions_per_period' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'features' => ['sometimes', 'array'],
            'features.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
