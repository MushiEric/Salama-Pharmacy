<?php

namespace App\Modules\Subscription\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:packages,slug'],
            'max_branches' => ['nullable', 'integer', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:0'],
            'max_devices' => ['nullable', 'integer', 'min:0'],
            'max_stock_items' => ['nullable', 'integer', 'min:0'],
            'max_transactions_per_period' => ['nullable', 'integer', 'min:0'],
            'features' => ['sometimes', 'array'],
            'features.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
