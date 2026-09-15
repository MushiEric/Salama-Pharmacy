<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\Enums\SystemRole;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleManagementService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly PermissionResolver $permissions,
    ) {}

    /**
     * @param  array{name: string, permission_codes?: list<string>}  $payload
     */
    public function create(array $payload): Role
    {
        $tenantId = $this->tenantContext->tenantId();
        $slug = $this->uniqueSlug($payload['name'], $tenantId);

        if (SystemRole::tryFrom($slug) !== null) {
            throw ValidationException::withMessages([
                'name' => 'This role name is reserved for a system role.',
            ]);
        }

        $role = Role::query()->create([
            'tenant_id' => $tenantId,
            'name' => $payload['name'],
            'slug' => $slug,
            'is_system' => false,
        ]);

        if (isset($payload['permission_codes'])) {
            $this->syncPermissions($role, $payload['permission_codes']);
        }

        return $role->load('permissions');
    }

    /**
     * @param  array{name?: string, permission_codes?: list<string>}  $payload
     */
    public function update(Role $role, array $payload): Role
    {
        $this->assertSameTenant($role);

        if ($role->isFixed() && isset($payload['name']) && $payload['name'] !== $role->name) {
            throw new AuthorizationException('Fixed system roles cannot be renamed.');
        }

        if (! $role->isFixed() && isset($payload['name'])) {
            $role->name = $payload['name'];
            $role->save();
        }

        if (isset($payload['permission_codes'])) {
            if ($role->isFixed()) {
                throw new AuthorizationException('Fixed system role permissions cannot be changed.');
            }

            $this->syncPermissions($role, $payload['permission_codes']);
        }

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        $this->assertSameTenant($role);

        if ($role->isFixed()) {
            throw new AuthorizationException('Fixed system roles cannot be deleted.');
        }

        $role->delete();
        $this->permissions->forgetForTenant($this->tenantContext->tenantId());
    }

    /**
     * @param  list<string>  $permissionCodes
     */
    public function syncPermissions(Role $role, array $permissionCodes): void
    {
        $this->assertSameTenant($role);

        $permissions = Permission::query()
            ->whereIn('code', $permissionCodes)
            ->get();

        if ($permissions->count() !== count(array_unique($permissionCodes))) {
            throw ValidationException::withMessages([
                'permission_codes' => 'One or more permissions are invalid.',
            ]);
        }

        $sync = [];

        foreach ($permissions as $permission) {
            $sync[$permission->id] = ['tenant_id' => $role->tenant_id];
        }

        $role->permissions()->sync($sync);
        $this->permissions->forgetForTenant($role->tenant_id);
    }

    /**
     * @param  list<string>  $roleIds
     */
    public function assignRoles(User $user, array $roleIds): User
    {
        $tenantId = $this->tenantContext->tenantId();

        if ($user->tenant_id !== $tenantId) {
            throw new AuthorizationException('Cannot assign roles across tenants.');
        }

        $roles = Role::query()->whereIn('id', $roleIds)->get();

        if ($roles->count() !== count(array_unique($roleIds))) {
            throw ValidationException::withMessages([
                'role_ids' => 'One or more roles are invalid for this tenant.',
            ]);
        }

        if ($roles->contains(fn (Role $role) => $role->slug === SystemRole::PlatformSuperadmin->value)) {
            throw new AuthorizationException('The platform superadmin role cannot be assigned to tenant users.');
        }

        $sync = [];

        foreach ($roles as $role) {
            $sync[$role->id] = ['tenant_id' => $tenantId];
        }

        $user->roles()->sync($sync);
        $this->permissions->forgetForTenant($tenantId);

        return $user->load('roles');
    }

    private function assertSameTenant(Role $role): void
    {
        if ($role->tenant_id !== $this->tenantContext->tenantId()) {
            throw new AuthorizationException('Role does not belong to the current tenant.');
        }
    }

    private function uniqueSlug(string $name, string $tenantId): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'role';
        $slug = $base;
        $i = 1;

        while (Role::query()->where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
