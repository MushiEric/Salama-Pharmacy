<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\Enums\SystemRole;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionResolver
{
    /**
     * @return list<string>
     */
    public function codesFor(User $user): array
    {
        if ($user->isPlatformSuperadmin()) {
            return ['*'];
        }

        $version = $this->currentVersion($user->tenant_id);

        /** @var list<string> $codes */
        $codes = Cache::remember(
            $this->userKey($user, $version),
            300,
            function () use ($user): array {
                return $user->roles()
                    ->withoutGlobalScopes()
                    ->with('permissions')
                    ->get()
                    ->flatMap(fn ($role) => $role->permissions->pluck('code'))
                    ->unique()
                    ->values()
                    ->all();
            },
        );

        return $codes;
    }

    public function userHas(User $user, string $code): bool
    {
        if ($user->isPlatformSuperadmin()) {
            return true;
        }

        if ($user->hasSystemRole(SystemRole::PharmacyAdmin) && $user->tenant_id !== null) {
            return true;
        }

        return in_array($code, $this->codesFor($user), true);
    }

    public function forgetForTenant(?string $tenantId): void
    {
        Cache::put($this->versionKey($tenantId), $this->currentVersion($tenantId) + 1, 86400);
    }

    private function currentVersion(?string $tenantId): int
    {
        return (int) Cache::get($this->versionKey($tenantId), 1);
    }

    private function versionKey(?string $tenantId): string
    {
        return 'rbac:ver:'.($tenantId ?? 'platform');
    }

    private function userKey(User $user, int $version): string
    {
        return 'rbac:'.$version.':'.($user->tenant_id ?? 'platform').':'.$user->id;
    }
}
