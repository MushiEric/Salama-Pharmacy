<?php

namespace Database\Factories;

use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PharmacyProduct>
 */
class PharmacyProductFactory extends Factory
{
    protected $model = PharmacyProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'master_drug_id' => null,
            'local_name' => fake()->unique()->words(2, true),
            'prescription_required' => false,
            'status' => ProductStatus::Active,
        ];
    }

    public function prescriptionRequired(): static
    {
        return $this->state(fn (): array => [
            'prescription_required' => true,
        ]);
    }
}
