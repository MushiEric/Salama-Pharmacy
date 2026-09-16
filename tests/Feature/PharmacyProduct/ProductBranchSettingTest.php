<?php

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Models\Branch;

test('pharmacy admin can set a branch selling price and reorder level', function () {
    $pharmacy = provisionPharmacy('branch-price');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $pharmacy['branch']->id,
            'selling_price' => 500,
            'reorder_level_base' => 200,
        ])
        ->assertCreated()
        ->assertJsonPath('data.selling_price', '500.00')
        ->assertJsonPath('data.reorder_level_base', '200.000');
});

test('the same product can have a different price in a different branch', function () {
    $pharmacy = provisionPharmacy('branch-price-diff');
    $secondBranch = Branch::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $pharmacy['branch']->id,
            'selling_price' => 200,
        ])
        ->assertCreated();

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $secondBranch->id,
            'selling_price' => 250,
        ])
        ->assertCreated();

    $this->actingAs($pharmacy['admin'])
        ->getJson("/api/products/{$product->id}/branch-settings")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('posting the same branch and product again updates rather than duplicates the setting', function () {
    $pharmacy = provisionPharmacy('branch-price-upsert');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $pharmacy['branch']->id,
            'selling_price' => 200,
        ])
        ->assertCreated();

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $pharmacy['branch']->id,
            'selling_price' => 300,
        ])
        ->assertOk()
        ->assertJsonPath('data.selling_price', '300.00');

    expect($product->branchSettings()->count())->toBe(1);
});

test('a tenant cannot set a price for a branch belonging to another tenant', function () {
    $alpha = provisionPharmacy('branch-price-iso-a');
    $beta = provisionPharmacy('branch-price-iso-b');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $alpha['tenant']->id]);

    $this->actingAs($alpha['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $beta['branch']->id,
            'selling_price' => 200,
        ])
        ->assertForbidden();
});

test('a negative selling price is rejected', function () {
    $pharmacy = provisionPharmacy('branch-price-negative');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/branch-settings", [
            'branch_id' => $pharmacy['branch']->id,
            'selling_price' => -10,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('selling_price');
});
