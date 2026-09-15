<?php

namespace App\Modules\Tenancy\Presentation\Http\Middleware;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Models\User;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->stripClientTenantIdentifiers($request);
        $this->tenantContext->clear();

        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        if ($user->status !== UserStatus::Active) {
            abort(403, 'User account is not active.');
        }

        if ($user->tenant_id === null) {
            if (! $user->isPlatformSuperadmin()) {
                abort(403, 'User is not assigned to a tenant.');
            }

            $this->tenantContext->enterPlatformContext();

            return $next($request);
        }

        $tenant = Tenant::query()->find($user->tenant_id);

        if ($tenant === null) {
            abort(403, 'Tenant could not be resolved from identity.');
        }

        if (! $tenant->isActive()) {
            abort(403, 'Tenant is not active.');
        }

        $this->tenantContext->setTenant($tenant);

        return $next($request);
    }

    private function stripClientTenantIdentifiers(Request $request): void
    {
        foreach (['tenant_id', 'tenantId'] as $key) {
            $request->request->remove($key);
            $request->query->remove($key);
        }

        $json = $request->json();

        if ($json->has('tenant_id')) {
            $json->remove('tenant_id');
        }

        if ($json->has('tenantId')) {
            $json->remove('tenantId');
        }
    }
}
