<?php

namespace App\Modules\Procurement\Application;

use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Domain\Enums\BatchStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use App\Modules\Procurement\Domain\Enums\StockReceiptStatus;
use App\Modules\Procurement\Models\StockReceipt;
use App\Modules\Tenancy\Application\BranchAccessService;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockReceivingService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly BranchAccessService $branchAccess,
    ) {}

    /**
     * @param  array{branch_id: string, supplier_id?: string|null, reference_no?: string|null, items: list<array{pharmacy_product_id: string, product_unit_id: string, quantity_in_unit: float, batch_number: string, manufactured_at?: string|null, expires_at?: string|null, unit_cost_base: float}>}  $payload
     */
    public function receive(array $payload, User $actor): StockReceipt
    {
        $branch = $this->branchAccess->assertBranchInCurrentTenant($payload['branch_id']);
        $tenantId = $this->tenantContext->tenantId();

        return DB::transaction(function () use ($payload, $branch, $tenantId, $actor): StockReceipt {
            $receipt = StockReceipt::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branch->id,
                'supplier_id' => $payload['supplier_id'] ?? null,
                'reference_no' => $payload['reference_no'] ?? null,
                'received_by_user_id' => $actor->id,
                'received_at' => now(),
                'status' => StockReceiptStatus::Posted->value,
            ]);

            foreach ($payload['items'] as $itemPayload) {
                $this->receiveItem($receipt, $branch, $tenantId, $itemPayload, $actor);
            }

            return $receipt->load('items.batch');
        });
    }

    /**
     * @param  array{pharmacy_product_id: string, product_unit_id: string, quantity_in_unit: float, batch_number: string, manufactured_at?: string|null, expires_at?: string|null, unit_cost_base: float}  $itemPayload
     */
    private function receiveItem(StockReceipt $receipt, Branch $branch, string $tenantId, array $itemPayload, User $actor): void
    {
        $unit = ProductUnit::query()->findOrFail($itemPayload['product_unit_id']);

        if ($unit->pharmacy_product_id !== $itemPayload['pharmacy_product_id']) {
            throw ValidationException::withMessages([
                'product_unit_id' => 'The unit does not belong to the specified product.',
            ]);
        }

        $quantityBase = $unit->toBase((float) $itemPayload['quantity_in_unit']);

        $batch = InventoryBatch::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branch->id,
            'pharmacy_product_id' => $itemPayload['pharmacy_product_id'],
            'supplier_id' => $receipt->supplier_id,
            'batch_number' => $itemPayload['batch_number'],
            'received_at' => now(),
            'manufactured_at' => $itemPayload['manufactured_at'] ?? null,
            'expires_at' => $itemPayload['expires_at'] ?? null,
            'initial_quantity_base' => $quantityBase,
            'available_quantity_base' => $quantityBase,
            'unit_cost_base' => $itemPayload['unit_cost_base'],
            'status' => BatchStatus::Active->value,
        ]);

        $item = $receipt->items()->create([
            'tenant_id' => $tenantId,
            'pharmacy_product_id' => $itemPayload['pharmacy_product_id'],
            'product_unit_id' => $unit->id,
            'quantity_in_unit' => $itemPayload['quantity_in_unit'],
            'quantity_base' => $quantityBase,
            'batch_number' => $itemPayload['batch_number'],
            'manufactured_at' => $itemPayload['manufactured_at'] ?? null,
            'expires_at' => $itemPayload['expires_at'] ?? null,
            'unit_cost_base' => $itemPayload['unit_cost_base'],
            'inventory_batch_id' => $batch->id,
        ]);

        InventoryMovement::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branch->id,
            'pharmacy_product_id' => $itemPayload['pharmacy_product_id'],
            'batch_id' => $batch->id,
            'type' => InventoryMovementType::Receipt->value,
            'quantity_delta_base' => $quantityBase,
            'reference_type' => 'stock_receipt_item',
            'reference_id' => $item->id,
            'actor_user_id' => $actor->id,
            'occurred_at' => now(),
        ]);
    }
}
