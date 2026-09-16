<?php

namespace Database\Factories;

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductBranchSetting;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductBranchSetting>
 */
class ProductBranchSettingFactory extends Factory
{
    protected $model = ProductBranchSetting::class;

    public function configure(): static
    {
        return $this->afterMaking(function (ProductBranchSetting $setting): void {
            if ($setting->tenant_id === null) {
                $setting->tenant_id = PharmacyProduct::withoutGlobalScopes()
                    ->whereKey($setting->pharmacy_product_id)
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
            'selling_price' => fake()->randomFloat(2, 100, 5000),
            'reorder_level_base' => fake()->randomFloat(3, 10, 100),
            'is_active' => true,
        ];
    }
}
