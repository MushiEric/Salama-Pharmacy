<?php

namespace App\Modules\Supplier\Providers;

use App\Modules\Supplier\Models\Supplier;
use App\Modules\Tenancy\Infrastructure\Persistence\TenantScopedBinding;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SupplierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('supplier', fn (string $value): Supplier => TenantScopedBinding::resolve(Supplier::class, $value));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Supplier/Presentation/Routes/api.php'));
    }
}
