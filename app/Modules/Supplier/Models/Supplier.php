<?php

namespace App\Modules\Supplier\Models;

use App\Modules\Supplier\Domain\Enums\SupplierStatus;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SupplierStatus::class,
        ];
    }

    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }
}
