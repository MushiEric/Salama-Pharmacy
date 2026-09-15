<?php

namespace Database\Seeders;

use App\Modules\Subscription\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $this->package('Basic', 'basic', [
            'max_branches' => 1,
            'max_users' => 5,
            'max_devices' => 2,
            'max_stock_items' => 500,
            'max_transactions_per_period' => 1000,
            'features' => [],
        ]);

        $this->package('Standard', 'standard', [
            'max_branches' => 3,
            'max_users' => 20,
            'max_devices' => 6,
            'max_stock_items' => 5000,
            'max_transactions_per_period' => 10000,
            'features' => ['advanced_reports'],
        ]);

        $this->package('Premium', 'premium', [
            'max_branches' => null,
            'max_users' => null,
            'max_devices' => null,
            'max_stock_items' => null,
            'max_transactions_per_period' => null,
            'features' => ['advanced_reports'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function package(string $name, string $slug, array $attributes): void
    {
        Package::query()->updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'is_active' => true, ...$attributes],
        );
    }
}
