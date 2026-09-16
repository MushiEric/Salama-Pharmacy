<?php

namespace Database\Factories;

use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    protected $model = ProductUnit::class;

    public function configure(): static
    {
        return $this->afterMaking(function (ProductUnit $unit): void {
            if ($unit->tenant_id === null) {
                $unit->tenant_id = PharmacyProduct::withoutGlobalScopes()
                    ->whereKey($unit->pharmacy_product_id)
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
            'pharmacy_product_id' => PharmacyProduct::factory(),
            'name' => 'Tablet',
            'symbol' => 'tab',
            'multiplier_to_base' => 1,
            'is_base' => true,
            'status' => ProductStatus::Active,
        ];
    }

    public function box(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Box',
            'symbol' => 'box',
            'multiplier_to_base' => 100,
            'is_base' => false,
        ]);
    }
}
