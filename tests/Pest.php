<?php

use App\Modules\Identity\Application\Services\TenantProvisioningService;
use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Subscription\Models\Package;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\PackageSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->seed(RbacSeeder::class);
        $this->seed(PackageSeeder::class);
    })
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * @return array{tenant: Tenant, branch: Branch, admin: User, role: Role, subscription: TenantSubscription}
 */
function provisionPharmacy(string $suffix = 'a', string $packageSlug = 'standard'): array
{
    $package = Package::query()->where('slug', $packageSlug)->firstOrFail();

    return app(TenantProvisioningService::class)->provision([
        'name' => 'Pharmacy '.strtoupper($suffix),
        'admin_name' => 'Admin '.$suffix,
        'admin_email' => 'admin-'.$suffix.'@pharmacy.test',
        'admin_password' => 'password',
        'branch_name' => 'Main '.$suffix,
        'package_id' => $package->id,
    ]);
}

function platformSuperadmin(): User
{
    return app(TenantProvisioningService::class)->createPlatformSuperadmin(
        'Platform Superadmin',
        'super-'.fake()->unique()->userName().'@salama.test',
        'password',
    );
}

/**
 * @param  array{tenant: Tenant, branch: Branch, admin: User, role: Role}  $pharmacy
 * @param  list<string>  $permissionCodes
 */
function operationalUser(array $pharmacy, array $permissionCodes = [PermissionCatalog::SALE_CREATE]): User
{
    $role = Role::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'name' => 'Cashier',
        'slug' => 'cashier-'.fake()->unique()->numerify('###'),
        'is_system' => false,
    ]);

    $permissions = Permission::query()->whereIn('code', $permissionCodes)->get();
    $sync = [];

    foreach ($permissions as $permission) {
        $sync[$permission->id] = ['tenant_id' => $pharmacy['tenant']->id];
    }

    $role->permissions()->sync($sync);

    $user = User::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
    ]);

    $user->roles()->attach($role->id, ['tenant_id' => $pharmacy['tenant']->id]);

    return $user;
}

function spa(): TestCase
{
    return test()->withHeaders([
        'Origin' => 'http://localhost',
        'Referer' => 'http://localhost/',
    ])->withoutMiddleware(ValidateCsrfToken::class);
}
