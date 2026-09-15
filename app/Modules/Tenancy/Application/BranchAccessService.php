<?php

namespace App\Modules\Tenancy\Application;

use App\Modules\Identity\Models\User;
use App\Modules\Tenancy\Models\Branch;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BranchAccessService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function authorize(?string $requestedBranchId, User $user): ?Branch
    {
        if ($user->isPlatformSuperadmin()) {
            return null;
        }

        if (! $this->tenantContext->hasTenant()) {
            throw new AccessDeniedHttpException('Tenant context is required for branch access.');
        }

        $tenantId = $this->tenantContext->tenantId();

        if ($user->isPharmacyAdmin()) {
            if ($requestedBranchId === null || $requestedBranchId === '') {
                return null;
            }

            return $this->branchInCurrentTenant($requestedBranchId, $tenantId);
        }

        if ($user->branch_id === null) {
            throw new AccessDeniedHttpException('User is not assigned to a branch.');
        }

        if ($requestedBranchId !== null && $requestedBranchId !== '' && $requestedBranchId !== $user->branch_id) {
            throw new AccessDeniedHttpException('User is not authorized for this branch.');
        }

        return $this->branchInCurrentTenant($user->branch_id, $tenantId);
    }

    public function assertBranchInCurrentTenant(string $branchId): Branch
    {
        return $this->branchInCurrentTenant($branchId, $this->tenantContext->tenantId());
    }

    private function branchInCurrentTenant(string $branchId, string $tenantId): Branch
    {
        $branch = Branch::withoutGlobalScopes()
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($branch === null) {
            throw new AccessDeniedHttpException('Branch does not belong to the current tenant.');
        }

        return $branch;
    }
}
