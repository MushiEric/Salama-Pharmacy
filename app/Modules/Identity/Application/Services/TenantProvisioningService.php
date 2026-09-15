<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\Enums\SystemRole;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Subscription\Application\TenantSubscriptionManagementService;
use App\Modules\Subscription\Models\Package;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Domain\Enums\BranchStatus;
use App\Modules\Tenancy\Domain\Enums\TenantStatus;
use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantProvisioningService
{
    public function __construct(
        private readonly PermissionResolver $permissions,
        private readonly TenantContext $tenantContext,
        private readonly TenantSubscriptionManagementService $subscriptions,
    ) {}

    /**
     * @param  array{name: string, admin_name: string, admin_email: string, admin_password: string, branch_name: string, branch_phone?: string|null, branch_address?: string|null, package_id: string}  $payload
     * @return array{tenant: Tenant, branch: Branch, admin: User, role: Role, subscription: TenantSubscription}
     */
    public function provision(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $tenant = Tenant::query()->create([
                'name' => $payload['name'],
                'status' => TenantStatus::Active,
                'settings' => [],
            ]);

            $this->tenantContext->setTenant($tenant);

            $package = Package::query()->findOrFail($payload['package_id']);
            $subscription = $this->subscriptions->createInitialSubscription($tenant, $package);

            $branch = Branch::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $payload['branch_name'],
                'phone' => $payload['branch_phone'] ?? null,
                'address' => $payload['branch_address'] ?? null,
                'status' => BranchStatus::Active,
            ]);

            $role = $this->createPharmacyAdminRole($tenant);

            $admin = User::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => null,
                'name' => $payload['admin_name'],
                'email' => $payload['admin_email'],
                'password' => $payload['admin_password'],
                'status' => UserStatus::Active,
            ]);

            $admin->roles()->attach($role->id, ['tenant_id' => $tenant->id]);

            return compact('tenant', 'branch', 'admin', 'role', 'subscription');
        });
    }

    public function createPharmacyAdminRole(Tenant $tenant): Role
    {
        $role = Role::withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'slug' => SystemRole::PharmacyAdmin->value,
            ],
            [
                'name' => SystemRole::PharmacyAdmin->displayName(),
                'is_system' => true,
            ],
        );

        $permissionIds = Permission::query()->pluck('id');
        $sync = [];

        foreach ($permissionIds as $permissionId) {
            $sync[$permissionId] = ['tenant_id' => $tenant->id];
        }

        $role->permissions()->sync($sync);
        $this->permissions->forgetForTenant($tenant->id);

        return $role;
    }

    public function ensurePlatformSuperadminRole(): Role
    {
        return Role::withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id' => null,
                'slug' => SystemRole::PlatformSuperadmin->value,
            ],
            [
                'name' => SystemRole::PlatformSuperadmin->displayName(),
                'is_system' => true,
            ],
        );
    }

    public function createPlatformSuperadmin(string $name, string $email, string $password): User
    {
        $existing = User::withoutGlobalScopes()->where('email', $email)->first();

        if ($existing !== null) {
            return $existing;
        }

        $role = $this->ensurePlatformSuperadminRole();

        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => null,
            'branch_id' => null,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'status' => UserStatus::Active,
        ]);

        $user->roles()->attach($role->id, ['tenant_id' => null]);

        return $user;
    }
}
