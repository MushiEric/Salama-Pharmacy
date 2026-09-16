<?php

namespace Database\Factories;

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Procurement\Models\StockReceipt;
use App\Modules\Procurement\Models\StockReceiptItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockReceiptItem>
 */
class StockReceiptItemFactory extends Factory
{
    protected $model = StockReceiptItem::class;

    public function configure(): static
    {
        return $this->afterMaking(function (StockReceiptItem $item): void {
            if ($item->tenant_id === null) {
                $item->tenant_id = StockReceipt::withoutGlobalScopes()
                    ->whereKey($item->stock_receipt_id)
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
            'stock_receipt_id' => StockReceipt::factory(),
            'pharmacy_product_id' => PharmacyProduct::factory(),
            'product_unit_id' => null,
            'quantity_in_unit' => fake()->randomFloat(3, 1, 50),
            'quantity_base' => fake()->randomFloat(3, 1, 50),
            'batch_number' => strtoupper(fake()->bothify('BATCH-####??')),
            'manufactured_at' => null,
            'expires_at' => now()->addYear(),
            'unit_cost_base' => fake()->randomFloat(6, 10, 500),
            'inventory_batch_id' => null,
        ];
    }
}
