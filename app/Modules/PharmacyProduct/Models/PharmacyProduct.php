<?php

namespace App\Modules\PharmacyProduct\Models;

use App\Modules\DrugCatalog\Models\MasterDrug;
use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Tenant;
use Database\Factories\PharmacyProductFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyProduct extends Model
{
    /** @use HasFactory<PharmacyProductFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'master_drug_id',
        'local_name',
        'prescription_required',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prescription_required' => 'boolean',
            'status' => ProductStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<MasterDrug, $this>
     */
    public function masterDrug(): BelongsTo
    {
        return $this->belongsTo(MasterDrug::class);
    }

    /**
     * @return HasMany<ProductUnit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * @return HasMany<ProductBranchSetting, $this>
     */
    public function branchSettings(): HasMany
    {
        return $this->hasMany(ProductBranchSetting::class);
    }

    public function baseUnit(): ?ProductUnit
    {
        return $this->units()->where('is_base', true)->first();
    }

    protected static function newFactory(): PharmacyProductFactory
    {
        return PharmacyProductFactory::new();
    }
}
