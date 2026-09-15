<?php

namespace App\Modules\Identity\Presentation\Http\Middleware;

use App\Modules\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformSuperadmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isPlatformSuperadmin()) {
            abort(403, 'Platform superadmin access required.');
        }

        return $next($request);
    }
}
