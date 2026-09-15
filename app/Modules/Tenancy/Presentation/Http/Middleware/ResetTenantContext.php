<?php

namespace App\Modules\Tenancy\Presentation\Http\Middleware;

use App\Modules\Tenancy\Application\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->tenantContext->clear();

        return $next($request);
    }
}
