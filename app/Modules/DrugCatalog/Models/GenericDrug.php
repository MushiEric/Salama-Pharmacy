<?php

namespace App\Modules\DrugCatalog\Models;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use Database\Factories\GenericDrugFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GenericDrug extends Model
{
    /** @use HasFactory<GenericDrugFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CatalogStatus::class,
        ];
    }

    /**
     * @return HasMany<MasterDrug, $this>
     */
    public function masterDrugs(): HasMany
    {
        return $this->hasMany(MasterDrug::class);
    }

    protected static function newFactory(): GenericDrugFactory
    {
        return GenericDrugFactory::new();
    }
}
