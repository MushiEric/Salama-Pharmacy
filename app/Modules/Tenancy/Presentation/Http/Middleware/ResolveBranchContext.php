<?php

namespace App\Modules\Tenancy\Presentation\Http\Middleware;

use App\Modules\Identity\Models\User;
use App\Modules\Tenancy\Application\BranchAccessService;
use App\Modules\Tenancy\Application\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveBranchContext
{
    public function __construct(
        private readonly BranchAccessService $branchAccess,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $requestedBranchId = $request->header('X-Branch-Id');

        if (! is_string($requestedBranchId) || $requestedBranchId === '') {
            $requestedBranchId = $request->query('branch_id');
            $requestedBranchId = is_string($requestedBranchId) ? $requestedBranchId : null;
        }

        $branch = $this->branchAccess->authorize($requestedBranchId, $user);
        $this->tenantContext->setBranch($branch);

        return $next($request);
    }
}
