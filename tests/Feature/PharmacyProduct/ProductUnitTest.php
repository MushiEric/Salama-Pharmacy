<?php

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductUnit;

test('pharmacy admin can add units to a product with base-unit conversion', function () {
    $pharmacy = provisionPharmacy('units-add');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/units", [
            'name' => 'Tablet',
            'symbol' => 'tab',
            'multiplier_to_base' => 1,
            'is_base' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_base', true);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/units", [
            'name' => 'Box',
            'symbol' => 'box',
            'multiplier_to_base' => 100,
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_base', false)
        ->assertJsonPath('data.multiplier_to_base', '100.000000');
});

test('setting a new base unit demotes the previous base unit', function () {
    $pharmacy = provisionPharmacy('units-rebase');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $tablet = ProductUnit::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'pharmacy_product_id' => $product->id,
        'is_base' => true,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/units", [
            'name' => 'Blister',
            'multiplier_to_base' => 10,
            'is_base' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_base', true);

    expect($tablet->fresh()->is_base)->toBeFalse();
});

test('a zero or negative multiplier is rejected', function () {
    $pharmacy = provisionPharmacy('units-invalid-multiplier');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson("/api/products/{$product->id}/units", [
            'name' => 'Broken',
            'multiplier_to_base' => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('multiplier_to_base');
});

test('a unit cannot be demoted from base without promoting another unit first', function () {
    $pharmacy = provisionPharmacy('units-demote-guard');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $tablet = ProductUnit::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'pharmacy_product_id' => $product->id,
        'is_base' => true,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->patchJson("/api/products/{$product->id}/units/{$tablet->id}", ['is_base' => false])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('is_base');
});

test('a unit belonging to a different product cannot be updated through this product route', function () {
    $pharmacy = provisionPharmacy('units-wrong-product');
    $productA = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $productB = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $unitOfB = ProductUnit::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'pharmacy_product_id' => $productB->id,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->patchJson("/api/products/{$productA->id}/units/{$unitOfB->id}", ['name' => 'Hijacked'])
        ->assertNotFound();
});
