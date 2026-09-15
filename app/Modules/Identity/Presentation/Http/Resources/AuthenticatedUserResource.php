<?php

namespace App\Modules\Identity\Presentation\Http\Resources;

use App\Modules\Identity\Models\User;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class AuthenticatedUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $context = app(TenantContext::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'tenant_id' => $this->tenant_id,
            'branch_id' => $context->branchId() ?? $this->branch_id,
            'is_platform_superadmin' => $this->isPlatformSuperadmin(),
            'is_pharmacy_admin' => $this->isPharmacyAdmin(),
            'permissions' => $this->permissionCodes(),
            'roles' => $this->roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'is_system' => $role->is_system,
            ]),
        ];
    }
}
