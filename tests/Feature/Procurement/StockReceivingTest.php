<?php

use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use App\Modules\Supplier\Models\Supplier;

test('receiving stock creates a batch and an inventory movement atomically, converting to base units', function () {
    $pharmacy = provisionPharmacy('receive-stock');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $box = ProductUnit::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'pharmacy_product_id' => $product->id,
        'name' => 'Box',
        'multiplier_to_base' => 100,
        'is_base' => false,
    ]);
    $supplier = Supplier::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);

    $response = $this->actingAs($pharmacy['admin'])
        ->postJson('/api/stock-receipts', [
            'branch_id' => $pharmacy['branch']->id,
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-1001',
            'items' => [[
                'pharmacy_product_id' => $product->id,
                'product_unit_id' => $box->id,
                'quantity_in_unit' => 5,
                'batch_number' => 'B-2026-01',
                'expires_at' => now()->addYear()->toDateString(),
                'unit_cost_base' => 250,
            ]],
        ])
        ->assertCreated();

    $response->assertJsonPath('data.reference_no', 'PO-1001')
        ->assertJsonCount(1, 'data.items');

    $batch = InventoryBatch::query()->where('pharmacy_product_id', $product->id)->firstOrFail();
    expect((float) $batch->available_quantity_base)->toBe(500.0)
        ->and((float) $batch->initial_quantity_base)->toBe(500.0)
        ->and($batch->batch_number)->toBe('B-2026-01')
        ->and($batch->supplier_id)->toBe($supplier->id);

    $movement = InventoryMovement::query()->where('batch_id', $batch->id)->firstOrFail();
    expect((float) $movement->quantity_delta_base)->toBe(500.0)
        ->and($movement->type)->toBe(InventoryMovementType::Receipt)
        ->and($movement->reference_type)->toBe('stock_receipt_item');
});

test('receiving supports multiple line items in one receipt', function () {
    $pharmacy = provisionPharmacy('receive-multi');
    $productA = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $productB = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $unitA = ProductUnit::factory()->create(['tenant_id' => $pharmacy['tenant']->id, 'pharmacy_product_id' => $productA->id]);
    $unitB = ProductUnit::factory()->create(['tenant_id' => $pharmacy['tenant']->id, 'pharmacy_product_id' => $productB->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/stock-receipts', [
            'branch_id' => $pharmacy['branch']->id,
            'items' => [
                [
                    'pharmacy_product_id' => $productA->id,
                    'product_unit_id' => $unitA->id,
                    'quantity_in_unit' => 10,
                    'batch_number' => 'A-1',
                    'unit_cost_base' => 100,
                ],
                [
                    'pharmacy_product_id' => $productB->id,
                    'product_unit_id' => $unitB->id,
                    'quantity_in_unit' => 20,
                    'batch_number' => 'B-1',
                    'unit_cost_base' => 50,
                ],
            ],
        ])
        ->assertCreated()
        ->assertJsonCount(2, 'data.items');

    expect(InventoryBatch::query()->count())->toBe(2);
});

test('a unit that does not belong to the specified product is rejected', function () {
    $pharmacy = provisionPharmacy('receive-wrong-unit');
    $productA = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $productB = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $unitOfB = ProductUnit::factory()->create(['tenant_id' => $pharmacy['tenant']->id, 'pharmacy_product_id' => $productB->id]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/stock-receipts', [
            'branch_id' => $pharmacy['branch']->id,
            'items' => [[
                'pharmacy_product_id' => $productA->id,
                'product_unit_id' => $unitOfB->id,
                'quantity_in_unit' => 5,
                'batch_number' => 'X-1',
                'unit_cost_base' => 10,
            ]],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('product_unit_id');
});

test('a tenant cannot receive stock into a branch belonging to another tenant', function () {
    $alpha = provisionPharmacy('receive-iso-a');
    $beta = provisionPharmacy('receive-iso-b');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $alpha['tenant']->id]);
    $unit = ProductUnit::factory()->create(['tenant_id' => $alpha['tenant']->id, 'pharmacy_product_id' => $product->id]);

    $this->actingAs($alpha['admin'])
        ->postJson('/api/stock-receipts', [
            'branch_id' => $beta['branch']->id,
            'items' => [[
                'pharmacy_product_id' => $product->id,
                'product_unit_id' => $unit->id,
                'quantity_in_unit' => 5,
                'batch_number' => 'X-1',
                'unit_cost_base' => 10,
            ]],
        ])
        ->assertForbidden();
});

test('operational users without inventory.receive permission cannot receive stock', function () {
    $pharmacy = provisionPharmacy('receive-permission');
    $cashier = operationalUser($pharmacy);
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $unit = ProductUnit::factory()->create(['tenant_id' => $pharmacy['tenant']->id, 'pharmacy_product_id' => $product->id]);

    $this->actingAs($cashier)
        ->postJson('/api/stock-receipts', [
            'branch_id' => $pharmacy['branch']->id,
            'items' => [[
                'pharmacy_product_id' => $product->id,
                'product_unit_id' => $unit->id,
                'quantity_in_unit' => 5,
                'batch_number' => 'X-1',
                'unit_cost_base' => 10,
            ]],
        ])
        ->assertForbidden();
});
