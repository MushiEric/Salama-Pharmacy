<?php

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Subscription\Application\TenantSubscriptionManagementService;
use App\Modules\Subscription\Models\Package;

test('pharmacy admin can create a local-only product without a master drug link', function () {
    $pharmacy = provisionPharmacy('local-product');

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/products', [
            'local_name' => 'House Brand Paracetamol',
        ])
        ->assertCreated()
        ->assertJsonPath('data.local_name', 'House Brand Paracetamol')
        ->assertJsonPath('data.tenant_id', $pharmacy['tenant']->id)
        ->assertJsonPath('data.master_drug_id', null);
});

test('operational users without product.create permission cannot create products', function () {
    $pharmacy = provisionPharmacy('no-product-permission');
    $cashier = operationalUser($pharmacy);

    $this->actingAs($cashier)
        ->postJson('/api/products', ['local_name' => 'Should Fail'])
        ->assertForbidden();
});

test('pharmacy admin can update a product', function () {
    $pharmacy = provisionPharmacy('update-product');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->patchJson("/api/products/{$product->id}", ['local_name' => 'Renamed'])
        ->assertOk()
        ->assertJsonPath('data.local_name', 'Renamed');
});

test('product creation is blocked once the package stock item limit is reached', function () {
    $pharmacy = provisionPharmacy('stock-limit');
    $tightPackage = Package::factory()->create(['max_stock_items' => 1]);

    app(TenantSubscriptionManagementService::class)->assign($pharmacy['tenant'], [
        'package_id' => $tightPackage->id,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/products', ['local_name' => 'First Product'])
        ->assertCreated();

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/products', ['local_name' => 'Second Product'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('limit');
});
