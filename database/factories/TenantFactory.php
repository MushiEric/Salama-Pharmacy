<?php

namespace Database\Factories;

use App\Modules\Tenancy\Domain\Enums\TenantStatus;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Pharmacy',
            'status' => TenantStatus::Active,
            'settings' => [],
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => TenantStatus::Suspended,
        ]);
    }
}
