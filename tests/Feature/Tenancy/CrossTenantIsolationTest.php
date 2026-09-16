<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Infrastructure\Persistence\TenantScope;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('a tenant cannot list another tenant\'s branches', function () {
    $alpha = provisionPharmacy('iso-a');
    $beta = provisionPharmacy('iso-b');

    $this->actingAs($alpha['admin'])
        ->getJson('/api/branches?tenant_id='.$beta['tenant']->id)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $alpha['branch']->id)
        ->assertJsonMissing(['id' => $beta['branch']->id]);
});

test('a tenant cannot fetch another tenant\'s branch by id', function () {
    $alpha = provisionPharmacy('iso-show-a');
    $beta = provisionPharmacy('iso-show-b');

    $this->actingAs($alpha['admin'])
        ->getJson('/api/branches/'.$beta['branch']->id)
        ->assertNotFound();
});

test('a tenant cannot list or fetch another tenant\'s users', function () {
    $alpha = provisionPharmacy('iso-user-a');
    $beta = provisionPharmacy('iso-user-b');

    $this->actingAs($alpha['admin'])
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $alpha['admin']->id);

    $this->actingAs($alpha['admin'])
        ->getJson('/api/users/'.$beta['admin']->id)
        ->assertNotFound();
});

test('client-supplied tenant_id is ignored when creating records', function () {
    $alpha = provisionPharmacy('iso-ignore-a');
    $beta = provisionPharmacy('iso-ignore-b');

    $this->actingAs($alpha['admin'])
        ->postJson('/api/users', [
            'name' => 'Intruder',
            'email' => 'intruder@pharmacy.test',
            'password' => 'password123',
            'tenant_id' => $beta['tenant']->id,
            'branch_id' => $alpha['branch']->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.tenant_id', $alpha['tenant']->id);
});

test('a tenant cannot assign a user to another tenant\'s branch', function () {
    $alpha = provisionPharmacy('iso-branch-a');
    $beta = provisionPharmacy('iso-branch-b');

    $this->actingAs($alpha['admin'])
        ->postJson('/api/users', [
            'name' => 'Wrong Branch',
            'email' => 'wrong-branch@pharmacy.test',
            'password' => 'password123',
            'branch_id' => $beta['branch']->id,
        ])
        ->assertForbidden();
});

test('a tenant cannot select another tenant\'s branch via header', function () {
    $alpha = provisionPharmacy('iso-header-a');
    $beta = provisionPharmacy('iso-header-b');

    $this->actingAs($alpha['admin'])
        ->withHeaders(['X-Branch-Id' => $beta['branch']->id])
        ->getJson('/api/user')
        ->assertForbidden();
});

test('a tenant cannot read or mutate another tenant\'s roles', function () {
    $alpha = provisionPharmacy('iso-role-a');
    $beta = provisionPharmacy('iso-role-b');

    $this->actingAs($alpha['admin'])
        ->getJson('/api/roles/'.$beta['role']->id)
        ->assertNotFound();

    $this->actingAs($alpha['admin'])
        ->patchJson('/api/roles/'.$beta['role']->id, [
            'permission_codes' => [PermissionCatalog::SALE_CREATE],
        ])
        ->assertNotFound();
});

test('eloquent tenant scope fails closed without context and isolates with context', function () {
    $alpha = provisionPharmacy('scope-a');
    $beta = provisionPharmacy('scope-b');

    app(TenantContext::class)->clear();

    expect(Branch::query()->count())->toBe(0)
        ->and(User::query()->count())->toBe(0)
        ->and(Role::query()->count())->toBe(0)
        ->and(TenantSubscription::query()->count())->toBe(0);

    app(TenantContext::class)->setTenant($alpha['tenant']);

    expect(Branch::query()->pluck('id')->all())->toBe([$alpha['branch']->id])
        ->and(User::query()->pluck('id')->all())->toBe([$alpha['admin']->id])
        ->and(Role::query()->pluck('id')->all())->toBe([$alpha['role']->id])
        ->and(Branch::query()->whereKey($beta['branch']->id)->exists())->toBeFalse()
        ->and(TenantSubscription::query()->pluck('id')->all())->toBe([$alpha['subscription']->id])
        ->and(TenantSubscription::query()->whereKey($beta['subscription']->id)->exists())->toBeFalse();
});

test('database rejects a user whose branch belongs to another tenant', function () {
    $alpha = provisionPharmacy('fk-user-a');
    $beta = provisionPharmacy('fk-user-b');

    expect(fn () => DB::table('users')->insert([
        'id' => (string) Str::uuid(),
        'tenant_id' => $alpha['tenant']->id,
        'branch_id' => $beta['branch']->id,
        'name' => 'Cross Tenant',
        'email' => 'cross-tenant@pharmacy.test',
        'password' => Hash::make('password'),
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('database rejects assigning another tenant\'s role to a user', function () {
    $alpha = provisionPharmacy('fk-role-a');
    $beta = provisionPharmacy('fk-role-b');

    expect(fn () => DB::table('user_roles')->insert([
        'tenant_id' => $alpha['tenant']->id,
        'user_id' => $alpha['admin']->id,
        'role_id' => $beta['role']->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('tenant scope remains the only implicit filter and is not disabled by client input', function () {
    $alpha = provisionPharmacy('scope-input-a');
    $beta = provisionPharmacy('scope-input-b');

    $this->actingAs($alpha['admin'])
        ->getJson('/api/branches', [
            'X-Tenant-Id' => $beta['tenant']->id,
        ])
        ->assertOk()
        ->assertJsonPath('data.0.id', $alpha['branch']->id)
        ->assertJsonMissing(['id' => $beta['branch']->id]);

    expect(array_key_exists(TenantScope::class, (new Branch)->getGlobalScopes()))->toBeTrue();
});

test('a tenant cannot fetch or mutate another tenant\'s product', function () {
    $alpha = provisionPharmacy('iso-product-a');
    $beta = provisionPharmacy('iso-product-b');
    $betaProduct = PharmacyProduct::factory()->create(['tenant_id' => $beta['tenant']->id]);

    $this->actingAs($alpha['admin'])
        ->getJson('/api/products/'.$betaProduct->id)
        ->assertNotFound();

    $this->actingAs($alpha['admin'])
        ->patchJson('/api/products/'.$betaProduct->id, ['local_name' => 'Hijacked'])
        ->assertNotFound();
});

test('a tenant cannot add a unit to another tenant\'s product', function () {
    $alpha = provisionPharmacy('iso-unit-a');
    $beta = provisionPharmacy('iso-unit-b');
    $betaProduct = PharmacyProduct::factory()->create(['tenant_id' => $beta['tenant']->id]);

    $this->actingAs($alpha['admin'])
        ->postJson("/api/products/{$betaProduct->id}/units", [
            'name' => 'Intruder Unit',
            'multiplier_to_base' => 1,
        ])
        ->assertNotFound();
});

test('a tenant\'s inventory balance and batch listing never include another tenant\'s stock', function () {
    $alpha = provisionPharmacy('iso-inventory-a');
    $beta = provisionPharmacy('iso-inventory-b');
    $alphaProduct = PharmacyProduct::factory()->create(['tenant_id' => $alpha['tenant']->id]);
    $betaProduct = PharmacyProduct::factory()->create(['tenant_id' => $beta['tenant']->id]);

    InventoryBatch::factory()->create([
        'tenant_id' => $alpha['tenant']->id,
        'branch_id' => $alpha['branch']->id,
        'pharmacy_product_id' => $alphaProduct->id,
        'available_quantity_base' => 40,
    ]);
    $betaBatch = InventoryBatch::factory()->create([
        'tenant_id' => $beta['tenant']->id,
        'branch_id' => $beta['branch']->id,
        'pharmacy_product_id' => $betaProduct->id,
        'available_quantity_base' => 999,
    ]);

    $this->actingAs($alpha['admin'])
        ->getJson('/api/inventory')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['pharmacy_product_id' => $betaProduct->id]);

    $this->actingAs($alpha['admin'])
        ->getJson('/api/inventory/batches')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $betaBatch->id]);
});
