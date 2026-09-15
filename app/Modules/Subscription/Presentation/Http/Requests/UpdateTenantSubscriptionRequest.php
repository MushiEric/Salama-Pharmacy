<?php

namespace App\Modules\Subscription\Presentation\Http\Requests;

use App\Modules\Subscription\Domain\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSubscriptionRequest extends FormRequest
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
            'package_id' => ['sometimes', 'uuid', 'exists:packages,id'],
            'status' => ['sometimes', Rule::enum(SubscriptionStatus::class)],
            'trial_ends_at' => ['sometimes', 'nullable', 'date'],
            'current_period_ends_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
