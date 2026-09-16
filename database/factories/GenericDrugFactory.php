<?php

namespace Database\Factories;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use App\Modules\DrugCatalog\Models\GenericDrug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GenericDrug>
 */
class GenericDrugFactory extends Factory
{
    protected $model = GenericDrug::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->randomElement(['acid', 'amine', 'ol', 'ine']),
            'status' => CatalogStatus::Active,
        ];
    }
}
