<?php

namespace Database\Factories;

use App\Modules\Supplier\Domain\Enums\SupplierStatus;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->company(),
            'contact_person' => fake()->name(),
            'phone' => fake()->e164PhoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'notes' => null,
            'status' => SupplierStatus::Active,
        ];
    }
}
