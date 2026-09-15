<?php

namespace App\Modules\Subscription\Presentation\Http\Middleware;

use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionWriteAccess
{
    /**
     * @var list<string>
     */
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private readonly SubscriptionEntitlementService $entitlements,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $this->entitlements->assertWriteAllowed();

        return $next($request);
    }
}
