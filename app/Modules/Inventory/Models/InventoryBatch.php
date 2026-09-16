<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Domain\Enums\BatchStatus;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Branch;
use Database\Factories\InventoryBatchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property BatchStatus $status
 * @property Carbon|null $expires_at
 * @property Carbon|null $manufactured_at
 * @property Carbon $received_at
 */
class InventoryBatch extends Model
{
    /** @use HasFactory<InventoryBatchFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'pharmacy_product_id',
        'supplier_id',
        'batch_number',
        'received_at',
        'manufactured_at',
        'expires_at',
        'initial_quantity_base',
        'available_quantity_base',
        'unit_cost_base',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'manufactured_at' => 'datetime',
            'expires_at' => 'datetime',
            'initial_quantity_base' => 'decimal:3',
            'available_quantity_base' => 'decimal:3',
            'unit_cost_base' => 'decimal:6',
            'status' => BatchStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<PharmacyProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'pharmacy_product_id');
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isDepleted(): bool
    {
        return (float) $this->available_quantity_base <= 0.0;
    }

    protected static function newFactory(): InventoryBatchFactory
    {
        return InventoryBatchFactory::new();
    }
}
