<?php

namespace Database\Factories;

use App\Modules\Inventory\Domain\Enums\AdjustmentReason;
use App\Modules\Inventory\Models\InventoryAdjustment;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryAdjustment>
 */
class InventoryAdjustmentFactory extends Factory
{
    protected $model = InventoryAdjustment::class;

    public function configure(): static
    {
        return $this->afterMaking(function (InventoryAdjustment $adjustment): void {
            if ($adjustment->tenant_id === null) {
                $adjustment->tenant_id = PharmacyProduct::withoutGlobalScopes()
                    ->whereKey($adjustment->pharmacy_product_id)
                    ->value('tenant_id');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'pharmacy_product_id' => PharmacyProduct::factory(),
            'batch_id' => InventoryBatch::factory(),
            'quantity_delta_base' => -1 * fake()->randomFloat(3, 1, 10),
            'reason' => AdjustmentReason::Damaged,
            'notes' => null,
            'created_by_user_id' => null,
        ];
    }
}
