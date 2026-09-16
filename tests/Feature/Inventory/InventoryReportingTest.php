<?php

use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductBranchSetting;

test('branch balance aggregates available quantity across batches', function () {
    $pharmacy = provisionPharmacy('balance-agg');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 30,
    ]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 20,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/inventory')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.available_quantity_base', '50.000');
});

test('batches endpoint lists batches ordered by nearest expiry with expiry status', function () {
    $pharmacy = provisionPharmacy('batches-list');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'expires_at' => now()->addDays(5),
    ]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'expires_at' => now()->addDays(200),
    ]);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/inventory/batches')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.expiry_status', 'red')
        ->assertJsonPath('data.1.expiry_status', 'green');
});

test('expiring endpoint only returns batches within the yellow threshold with stock remaining', function () {
    $pharmacy = provisionPharmacy('expiring-filter');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $soon = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'expires_at' => now()->addDays(10),
    ]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'expires_at' => now()->addDays(200),
    ]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'expires_at' => now()->addDays(10),
        'available_quantity_base' => 0,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/inventory/expiring')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $soon->id);
});

test('low stock endpoint flags products at or below their reorder level', function () {
    $pharmacy = provisionPharmacy('low-stock-filter');
    $lowProduct = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $healthyProduct = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    ProductBranchSetting::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $lowProduct->id,
        'reorder_level_base' => 50,
    ]);
    ProductBranchSetting::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $healthyProduct->id,
        'reorder_level_base' => 10,
    ]);

    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $lowProduct->id,
        'available_quantity_base' => 20,
    ]);
    InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $healthyProduct->id,
        'available_quantity_base' => 100,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/inventory/low-stock')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.pharmacy_product_id', $lowProduct->id);
});

test('a product never received at all still shows as low stock', function () {
    $pharmacy = provisionPharmacy('low-stock-never-received');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    ProductBranchSetting::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'reorder_level_base' => 5,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/inventory/low-stock')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.available_quantity_base', '0');
});
