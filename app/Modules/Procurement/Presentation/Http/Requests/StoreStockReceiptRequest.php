<?php

namespace App\Modules\Procurement\Presentation\Http\Requests;

use App\Modules\Identity\Domain\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionCatalog::INVENTORY_RECEIVE) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'uuid'],
            'supplier_id' => ['nullable', 'uuid', 'exists:suppliers,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.pharmacy_product_id' => ['required', 'uuid', 'exists:pharmacy_products,id'],
            'items.*.product_unit_id' => ['required', 'uuid', 'exists:product_units,id'],
            'items.*.quantity_in_unit' => ['required', 'numeric', 'gt:0'],
            'items.*.batch_number' => ['required', 'string', 'max:255'],
            'items.*.manufactured_at' => ['nullable', 'date'],
            'items.*.expires_at' => ['nullable', 'date', 'after:items.*.manufactured_at'],
            'items.*.unit_cost_base' => ['required', 'numeric', 'min:0'],
        ];
    }
}
