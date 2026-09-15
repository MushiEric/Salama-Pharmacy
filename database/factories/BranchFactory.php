<?php

namespace Database\Factories;

use App\Modules\Tenancy\Domain\Enums\BranchStatus;
use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->city().' Branch',
            'phone' => fake()->optional()->e164PhoneNumber(),
            'address' => fake()->optional()->address(),
            'status' => BranchStatus::Active,
        ];
    }
}
