<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Supplier\Models\Supplier;

test('pharmacy admin can create and update a supplier', function () {
    $pharmacy = provisionPharmacy('supplier-crud');

    $response = $this->actingAs($pharmacy['admin'])
        ->postJson('/api/suppliers', [
            'name' => 'Acme Pharma Distributors',
            'contact_person' => 'Jane Doe',
            'phone' => '+255700000000',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Acme Pharma Distributors');

    $supplierId = $response->json('data.id');

    $this->actingAs($pharmacy['admin'])
        ->patchJson("/api/suppliers/{$supplierId}", ['status' => 'inactive'])
        ->assertOk()
        ->assertJsonPath('data.status', 'inactive');
});

test('operational users without supplier.manage cannot create suppliers but can view them', function () {
    $pharmacy = provisionPharmacy('supplier-permissions');
    $cashier = operationalUser($pharmacy, [PermissionCatalog::SUPPLIER_VIEW]);
    Supplier::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($cashier)
        ->postJson('/api/suppliers', ['name' => 'Should Fail'])
        ->assertForbidden();

    $this->actingAs($cashier)
        ->getJson('/api/suppliers')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('a tenant cannot view or mutate another tenant\'s supplier', function () {
    $alpha = provisionPharmacy('supplier-iso-a');
    $beta = provisionPharmacy('supplier-iso-b');
    $betaSupplier = Supplier::factory()->create(['tenant_id' => $beta['tenant']->id]);

    $this->actingAs($alpha['admin'])
        ->getJson('/api/suppliers/'.$betaSupplier->id)
        ->assertNotFound();

    $this->actingAs($alpha['admin'])
        ->patchJson('/api/suppliers/'.$betaSupplier->id, ['name' => 'Hijacked'])
        ->assertNotFound();
});
