<?php

namespace Database\Factories;

use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function configure(): static
    {
        return $this->afterMaking(function (InventoryMovement $movement): void {
            if ($movement->tenant_id === null) {
                $movement->tenant_id = PharmacyProduct::withoutGlobalScopes()
                    ->whereKey($movement->pharmacy_product_id)
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
            'batch_id' => null,
            'type' => InventoryMovementType::Receipt,
            'quantity_delta_base' => fake()->randomFloat(3, 1, 100),
            'reference_type' => null,
            'reference_id' => null,
            'reason' => null,
            'actor_user_id' => null,
            'occurred_at' => now(),
            'metadata' => null,
        ];
    }
}
