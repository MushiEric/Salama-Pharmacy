<?php

namespace Database\Seeders;

use App\Modules\Identity\Application\Services\TenantProvisioningService;
use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Identity\Models\Permission;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $code => $description) {
            Permission::query()->firstOrCreate(
                ['code' => $code],
                ['description' => $description],
            );
        }

        app(TenantProvisioningService::class)->ensurePlatformSuperadminRole();
    }
}
