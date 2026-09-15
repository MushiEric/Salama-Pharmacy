<?php

namespace App\Modules\Identity\Providers;

use App\Modules\Identity\Infrastructure\Auth\TenantAwareUserProvider;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::provider('tenant-eloquent', function ($app, array $config): TenantAwareUserProvider {
            /** @var class-string<User> $model */
            $model = $config['model'];

            return new TenantAwareUserProvider($app['hash'], $model);
        });

        Gate::before(function (mixed $user, string $ability): ?bool {
            if ($user instanceof User && $user->isPlatformSuperadmin()) {
                return true;
            }

            if ($user instanceof User && $user->hasPermission($ability)) {
                return true;
            }

            return null;
        });

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Identity/Presentation/Routes/api.php'));
    }
}
