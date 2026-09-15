<?php

namespace App\Modules\Identity\Presentation\Http\Middleware;

use App\Modules\Tenancy\Application\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->hasTenant()) {
            abort(403, 'This action requires a tenant user.');
        }

        return $next($request);
    }
}
