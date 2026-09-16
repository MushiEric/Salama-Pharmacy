<?php

namespace App\Modules\Inventory\Application;

use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Domain\Enums\BatchStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Models\InventoryAdjustment;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InventoryAdjustmentService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array{batch_id: string, quantity_delta_base: float, reason: string, notes?: string|null}  $payload
     */
    public function adjust(array $payload, User $actor): InventoryAdjustment
    {
        return DB::transaction(function () use ($payload, $actor): InventoryAdjustment {
            $batch = InventoryBatch::query()->lockForUpdate()->findOrFail($payload['batch_id']);

            if ($this->tenantContext->hasBranch() && $batch->branch_id !== $this->tenantContext->branchId()) {
                throw new AccessDeniedHttpException('Batch does not belong to the current branch.');
            }

            $delta = (float) $payload['quantity_delta_base'];
            $newAvailable = (float) $batch->available_quantity_base + $delta;

            if ($newAvailable < 0) {
                throw ValidationException::withMessages([
                    'quantity_delta_base' => 'This adjustment would make the batch quantity negative.',
                ]);
            }

            $batch->available_quantity_base = $newAvailable;
            $batch->status = $newAvailable > 0 ? BatchStatus::Active : BatchStatus::Depleted;
            $batch->save();

            $adjustment = InventoryAdjustment::query()->create([
                'tenant_id' => $batch->tenant_id,
                'branch_id' => $batch->branch_id,
                'pharmacy_product_id' => $batch->pharmacy_product_id,
                'batch_id' => $batch->id,
                'quantity_delta_base' => $delta,
                'reason' => $payload['reason'],
                'notes' => $payload['notes'] ?? null,
                'created_by_user_id' => $actor->id,
            ]);

            InventoryMovement::query()->create([
                'tenant_id' => $batch->tenant_id,
                'branch_id' => $batch->branch_id,
                'pharmacy_product_id' => $batch->pharmacy_product_id,
                'batch_id' => $batch->id,
                'type' => ($delta >= 0 ? InventoryMovementType::AdjustmentIn : InventoryMovementType::AdjustmentOut)->value,
                'quantity_delta_base' => $delta,
                'reference_type' => 'inventory_adjustment',
                'reference_id' => $adjustment->id,
                'reason' => $payload['reason'],
                'actor_user_id' => $actor->id,
                'occurred_at' => now(),
            ]);

            return $adjustment;
        });
    }
}
