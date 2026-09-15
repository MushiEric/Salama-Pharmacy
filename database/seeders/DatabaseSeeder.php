<?php

namespace Database\Seeders;

use App\Modules\Identity\Application\Services\TenantProvisioningService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);
        $this->call(PackageSeeder::class);

        app(TenantProvisioningService::class)->createPlatformSuperadmin(
            'Platform Superadmin',
            'superadmin@salamapharma.test',
            'password',
        );
    }
}
