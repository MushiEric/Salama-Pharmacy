<?php

namespace App\Modules\DrugCatalog\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DrugCatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/DrugCatalog/Presentation/Routes/api.php'));
    }
}
