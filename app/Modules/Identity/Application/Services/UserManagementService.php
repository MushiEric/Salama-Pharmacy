<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Models\User;
use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use App\Modules\Tenancy\Application\BranchAccessService;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly BranchAccessService $branchAccess,
        private readonly RoleManagementService $roles,
        private readonly PermissionResolver $permissions,
        private readonly SubscriptionEntitlementService $entitlements,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, phone?: string|null, branch_id?: string|null, status?: string, role_ids?: list<string>}  $payload
     */
    public function create(array $payload): User
    {
        $this->entitlements->assertUserLimit();

        $tenantId = $this->tenantContext->tenantId();
        $branchId = $payload['branch_id'] ?? null;

        if (is_string($branchId) && $branchId !== '') {
            $this->branchAccess->assertBranchInCurrentTenant($branchId);
        } else {
            $branchId = null;
        }

        $user = User::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => $payload['password'],
            'status' => $payload['status'] ?? UserStatus::Active->value,
        ]);

        if (isset($payload['role_ids'])) {
            $this->roles->assignRoles($user, $payload['role_ids']);
        }

        $this->permissions->forgetForTenant($tenantId);

        return $user->load('roles');
    }

    /**
     * @param  array{name?: string, email?: string, phone?: string|null, password?: string, branch_id?: string|null, status?: string, role_ids?: list<string>}  $payload
     */
    public function update(User $user, array $payload): User
    {
        if ($user->tenant_id !== $this->tenantContext->tenantId()) {
            throw new AuthorizationException('Cannot update a user from another tenant.');
        }

        if (array_key_exists('branch_id', $payload)) {
            $branchId = $payload['branch_id'];

            if (is_string($branchId) && $branchId !== '') {
                $this->branchAccess->assertBranchInCurrentTenant($branchId);
                $user->branch_id = $branchId;
            } else {
                $user->branch_id = null;
            }
        }

        if (isset($payload['name'])) {
            $user->name = $payload['name'];
        }

        if (isset($payload['email'])) {
            $user->email = $payload['email'];
        }

        if (array_key_exists('phone', $payload)) {
            $user->phone = $payload['phone'];
        }

        if (isset($payload['password'])) {
            $user->password = $payload['password'];
        }

        if (isset($payload['status'])) {
            $user->status = UserStatus::from($payload['status']);
        }

        $user->save();

        if (isset($payload['role_ids'])) {
            $this->roles->assignRoles($user, $payload['role_ids']);
        }

        $this->permissions->forgetForTenant($user->tenant_id);

        return $user->load('roles');
    }

    public function assertSameTenant(User $user): void
    {
        if ($user->tenant_id !== $this->tenantContext->tenantId()) {
            throw ValidationException::withMessages([
                'user' => 'User does not belong to the current tenant.',
            ]);
        }
    }
}
