<?php

namespace App\Modules\Identity\Presentation\Http\Middleware;

use App\Modules\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->hasPermission($permission)) {
            abort(403, 'Missing permission: '.$permission);
        }

        return $next($request);
    }
}
