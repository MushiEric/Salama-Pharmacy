<?php

namespace Database\Factories;

use App\Modules\Inventory\Domain\Enums\BatchStatus;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryBatch>
 */
class InventoryBatchFactory extends Factory
{
    protected $model = InventoryBatch::class;

    public function configure(): static
    {
        return $this->afterMaking(function (InventoryBatch $batch): void {
            if ($batch->tenant_id === null) {
                $batch->tenant_id = PharmacyProduct::withoutGlobalScopes()
                    ->whereKey($batch->pharmacy_product_id)
                    ->value('tenant_id');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(3, 100, 1000);

        return [
            'branch_id' => Branch::factory(),
            'pharmacy_product_id' => PharmacyProduct::factory(),
            'supplier_id' => null,
            'batch_number' => strtoupper(fake()->bothify('BATCH-####??')),
            'received_at' => now(),
            'manufactured_at' => null,
            'expires_at' => now()->addYear(),
            'initial_quantity_base' => $quantity,
            'available_quantity_base' => $quantity,
            'unit_cost_base' => fake()->randomFloat(6, 10, 500),
            'status' => BatchStatus::Active,
        ];
    }

    public function expiringInDays(int $days): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->addDays($days),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function depleted(): static
    {
        return $this->state(fn (): array => [
            'available_quantity_base' => 0,
        ]);
    }
}
