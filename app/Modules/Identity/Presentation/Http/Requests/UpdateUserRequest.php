<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::USER_UPDATE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'branch_id' => ['nullable', 'uuid'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['uuid'],
        ];
    }
}
