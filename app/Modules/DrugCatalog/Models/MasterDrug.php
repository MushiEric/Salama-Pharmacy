<?php

namespace App\Modules\DrugCatalog\Models;

use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use Database\Factories\MasterDrugFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterDrug extends Model
{
    /** @use HasFactory<MasterDrugFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'generic_drug_id',
        'brand_name',
        'dosage_form',
        'strength',
        'manufacturer',
        'barcode',
        'category',
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
     * @return BelongsTo<GenericDrug, $this>
     */
    public function genericDrug(): BelongsTo
    {
        return $this->belongsTo(GenericDrug::class);
    }

    protected static function newFactory(): MasterDrugFactory
    {
        return MasterDrugFactory::new();
    }
}
