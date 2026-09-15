<?php

namespace Database\Factories;

use App\Modules\Subscription\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'max_branches' => 1,
            'max_users' => 5,
            'max_devices' => 2,
            'max_stock_items' => null,
            'max_transactions_per_period' => null,
            'features' => [],
            'is_active' => true,
        ];
    }

    public function unlimited(): static
    {
        return $this->state(fn (): array => [
            'max_branches' => null,
            'max_users' => null,
            'max_devices' => null,
        ]);
    }
}
