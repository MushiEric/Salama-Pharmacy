<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Domain\Policies\BatchSaleEligibilityPolicy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BatchSaleEligibilityPolicy::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Inventory/Presentation/Routes/api.php'));
    }
}
