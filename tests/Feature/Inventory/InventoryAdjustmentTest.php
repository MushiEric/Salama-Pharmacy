<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Inventory\Domain\Enums\BatchStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Models\Branch;

test('pharmacy admin can adjust stock downward with a reason', function () {
    $pharmacy = provisionPharmacy('adjust-down');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $batch = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 100,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => -10,
            'reason' => 'damaged',
        ])
        ->assertCreated()
        ->assertJsonPath('data.quantity_delta_base', '-10.000');

    expect((float) $batch->fresh()->available_quantity_base)->toBe(90.0);

    $movement = InventoryMovement::query()->where('batch_id', $batch->id)->firstOrFail();
    expect($movement->type)->toBe(InventoryMovementType::AdjustmentOut)
        ->and((float) $movement->quantity_delta_base)->toBe(-10.0);
});

test('a correction can increase stock', function () {
    $pharmacy = provisionPharmacy('adjust-up');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $batch = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 50,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => 5,
            'reason' => 'correction',
        ])
        ->assertCreated();

    expect((float) $batch->fresh()->available_quantity_base)->toBe(55.0);

    $movement = InventoryMovement::query()->where('batch_id', $batch->id)->firstOrFail();
    expect($movement->type)->toBe(InventoryMovementType::AdjustmentIn);
});

test('an adjustment cannot make batch stock negative', function () {
    $pharmacy = provisionPharmacy('adjust-negative-guard');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $batch = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 5,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => -10,
            'reason' => 'lost',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('quantity_delta_base');

    expect((float) $batch->fresh()->available_quantity_base)->toBe(5.0);
});

test('a batch fully depleted by an adjustment is marked depleted', function () {
    $pharmacy = provisionPharmacy('adjust-deplete');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $batch = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 10,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => -10,
            'reason' => 'expired',
        ])
        ->assertCreated();

    expect($batch->fresh()->status)->toBe(BatchStatus::Depleted);
});

test('reason other requires notes', function () {
    $pharmacy = provisionPharmacy('adjust-other-notes');
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $batch = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $pharmacy['branch']->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 10,
    ]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => -1,
            'reason' => 'other',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('notes');

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => -1,
            'reason' => 'other',
            'notes' => 'Explaining the discrepancy.',
        ])
        ->assertCreated();
});

test('an operational user cannot adjust stock in a branch other than their own', function () {
    $pharmacy = provisionPharmacy('adjust-branch-guard');
    $cashier = operationalUser($pharmacy, [PermissionCatalog::INVENTORY_ADJUST]);
    $otherBranch = Branch::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $product = PharmacyProduct::factory()->create(['tenant_id' => $pharmacy['tenant']->id]);
    $batch = InventoryBatch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'branch_id' => $otherBranch->id,
        'pharmacy_product_id' => $product->id,
        'available_quantity_base' => 10,
    ]);

    $this->actingAs($cashier)
        ->postJson('/api/inventory/adjustments', [
            'batch_id' => $batch->id,
            'quantity_delta_base' => -1,
            'reason' => 'lost',
        ])
        ->assertForbidden();
});
