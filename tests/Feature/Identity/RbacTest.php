<?php

use App\Modules\Identity\Domain\Enums\SystemRole;
use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Identity\Models\Role;
use App\Modules\Subscription\Models\Package;

test('pharmacy admin can create a dynamic operational role with permissions', function () {
    $pharmacy = provisionPharmacy('rbac');

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/roles', [
            'name' => 'Pharmacist',
            'permission_codes' => [
                PermissionCatalog::SALE_CREATE,
                PermissionCatalog::PRESCRIPTION_DISPENSE,
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Pharmacist')
        ->assertJsonPath('data.is_system', false)
        ->assertJsonCount(2, 'data.permissions');
});

test('fixed pharmacy admin role cannot be deleted or renamed', function () {
    $pharmacy = provisionPharmacy('fixed');

    $this->actingAs($pharmacy['admin'])
        ->patchJson('/api/roles/'.$pharmacy['role']->id, [
            'name' => 'Owner',
        ])
        ->assertForbidden();

    $this->actingAs($pharmacy['admin'])
        ->deleteJson('/api/roles/'.$pharmacy['role']->id)
        ->assertForbidden();

    expect($pharmacy['role']->fresh()->slug)->toBe(SystemRole::PharmacyAdmin->value);
});

test('operational users are denied actions they lack permission for', function () {
    $pharmacy = provisionPharmacy('cashier');
    $cashier = operationalUser($pharmacy, [PermissionCatalog::SALE_CREATE]);

    $this->actingAs($cashier)
        ->getJson('/api/roles')
        ->assertForbidden();

    $this->actingAs($cashier)
        ->postJson('/api/users', [
            'name' => 'Other',
            'email' => 'other@pharmacy.test',
            'password' => 'password123',
            'branch_id' => $pharmacy['branch']->id,
        ])
        ->assertForbidden();
});

test('pharmacy admin can assign a dynamic role to a tenant user', function () {
    $pharmacy = provisionPharmacy('assign');
    $cashier = operationalUser($pharmacy, [PermissionCatalog::SALE_CREATE]);

    $role = Role::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'name' => 'Store Keeper',
        'slug' => 'store-keeper',
        'is_system' => false,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->patchJson('/api/users/'.$cashier->id, [
            'role_ids' => [$role->id],
        ])
        ->assertOk()
        ->assertJsonPath('data.roles.0.slug', 'store-keeper');
});

test('tenant users cannot list or mutate platform tenants', function () {
    $pharmacy = provisionPharmacy('noplatform');

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/platform/tenants')
        ->assertForbidden();
});

test('platform superadmin can provision a tenant', function () {
    $superadmin = platformSuperadmin();
    $package = Package::query()->where('slug', 'standard')->firstOrFail();

    $this->actingAs($superadmin)
        ->postJson('/api/platform/tenants', [
            'name' => 'New Pharmacy',
            'admin_name' => 'Owner',
            'admin_email' => 'owner@newpharmacy.test',
            'admin_password' => 'password123',
            'branch_name' => 'HQ',
            'package_id' => $package->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'New Pharmacy')
        ->assertJsonPath('subscription.status', 'trial');
});
