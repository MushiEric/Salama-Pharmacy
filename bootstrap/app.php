<?php

use App\Modules\Identity\Presentation\Http\Middleware\EnsurePermission;
use App\Modules\Identity\Presentation\Http\Middleware\EnsurePlatformSuperadmin;
use App\Modules\Identity\Presentation\Http\Middleware\EnsureTenantUser;
use App\Modules\Subscription\Presentation\Http\Middleware\EnforceSubscriptionWriteAccess;
use App\Modules\Tenancy\Presentation\Http\Middleware\ResetTenantContext;
use App\Modules\Tenancy\Presentation\Http\Middleware\ResolveBranchContext;
use App\Modules\Tenancy\Presentation\Http\Middleware\ResolveTenantContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->prependToGroup('api', ResetTenantContext::class);
        $middleware->prependToGroup('web', ResetTenantContext::class);

        $middleware->alias([
            'tenant.context' => ResolveTenantContext::class,
            'branch.context' => ResolveBranchContext::class,
            'permission' => EnsurePermission::class,
            'platform' => EnsurePlatformSuperadmin::class,
        ]);

        // Every tenant-scoped route group requires an authenticated tenant user
        // AND is subject to subscription-driven read-only enforcement. Defined
        // as a group (not a single alias) so both checks apply everywhere
        // 'tenant' is used, without each module having to remember the second.
        $middleware->group('tenant', [
            EnsureTenantUser::class,
            EnforceSubscriptionWriteAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
