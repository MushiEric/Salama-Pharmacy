<?php

use App\Modules\DrugCatalog\Providers\DrugCatalogServiceProvider;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Inventory\Providers\InventoryServiceProvider;
use App\Modules\PharmacyProduct\Providers\PharmacyProductServiceProvider;
use App\Modules\Procurement\Providers\ProcurementServiceProvider;
use App\Modules\Subscription\Providers\SubscriptionServiceProvider;
use App\Modules\Supplier\Providers\SupplierServiceProvider;
use App\Modules\Tenancy\Providers\TenancyServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    TenancyServiceProvider::class,
    IdentityServiceProvider::class,
    SubscriptionServiceProvider::class,
    DrugCatalogServiceProvider::class,
    PharmacyProductServiceProvider::class,
    SupplierServiceProvider::class,
    InventoryServiceProvider::class,
    ProcurementServiceProvider::class,
];
