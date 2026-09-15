<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Presentation\Http\Resources\AuthenticatedUserResource;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, false)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== UserStatus::Active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'User account is not active.');
        }

        if ($user->tenant_id !== null) {
            $tenant = Tenant::query()->find($user->tenant_id);

            if ($tenant === null || ! $tenant->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(403, 'Tenant is not active.');
            }
        }

        $user->load('roles');

        return (new AuthenticatedUserResource($user))
            ->response()
            ->setStatusCode(200);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::forgetGuards();

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): AuthenticatedUserResource
    {
        /** @var User $user */
        $user = $request->user();
        $user->load('roles');

        return new AuthenticatedUserResource($user);
    }
}
