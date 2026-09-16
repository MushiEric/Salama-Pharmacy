<?php

namespace Database\Factories;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use App\Modules\DrugCatalog\Models\GenericDrug;
use App\Modules\DrugCatalog\Models\MasterDrug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterDrug>
 */
class MasterDrugFactory extends Factory
{
    protected $model = MasterDrug::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'generic_drug_id' => GenericDrug::factory(),
            'brand_name' => fake()->unique()->company(),
            'dosage_form' => fake()->randomElement(['Tablet', 'Syrup', 'Injection', 'Capsule']),
            'strength' => fake()->randomElement(['250mg', '500mg', '5mg/ml']),
            'manufacturer' => fake()->company(),
            'barcode' => fake()->unique()->ean13(),
            'category' => fake()->randomElement(['Analgesic', 'Antibiotic', 'Antimalarial']),
            'status' => CatalogStatus::Active,
        ];
    }
}
