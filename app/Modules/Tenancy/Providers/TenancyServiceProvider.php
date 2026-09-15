<?php

namespace App\Modules\Tenancy\Providers;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Infrastructure\Persistence\TenantScopedBinding;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        Route::bind('branch', fn (string $value): Branch => TenantScopedBinding::resolve(Branch::class, $value));
        Route::bind('user', fn (string $value): User => TenantScopedBinding::resolve(User::class, $value));
        Route::bind('role', fn (string $value): Role => TenantScopedBinding::resolve(Role::class, $value));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Tenancy/Presentation/Routes/api.php'));
    }
}
