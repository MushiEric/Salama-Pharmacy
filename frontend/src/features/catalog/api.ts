import { queryOptions } from '@tanstack/react-query';
import { apiGet, apiGetPaginated, apiSend } from '@/shared/api/client';

export type MasterDrug = {
    id: string;
    generic_drug_id: string;
    generic_drug_name?: string;
    brand_name: string;
    dosage_form: string;
    strength: string;
    manufacturer: string | null;
    barcode: string | null;
    category: string | null;
    status: string;
};

export type ProductUnit = {
    id: string;
    pharmacy_product_id: string;
    name: string;
    symbol: string | null;
    multiplier_to_base: string | number;
    is_base: boolean;
    status: 'active' | 'inactive';
};

export type ProductBranchSetting = {
    id: string;
    branch_id: string;
    pharmacy_product_id: string;
    selling_price: string | number;
    reorder_level_base: string | number;
    is_active: boolean;
};

export type PharmacyProduct = {
    id: string;
    tenant_id: string;
    master_drug_id: string | null;
    local_name: string;
    prescription_required: boolean;
    status: 'active' | 'inactive';
    units?: ProductUnit[];
    branch_settings?: ProductBranchSetting[];
};

export type ProductPayload = {
    master_drug_id?: string | null;
    local_name: string;
    prescription_required?: boolean;
    status?: 'active' | 'inactive';
};

export type UnitPayload = {
    name: string;
    symbol?: string | null;
    multiplier_to_base: number;
    is_base?: boolean;
    status?: 'active' | 'inactive';
};

export type BranchSettingPayload = {
    branch_id: string;
    selling_price: number;
    reorder_level_base?: number;
    is_active?: boolean;
};

export function masterDrugsQueryOptions(search: string, page = 1) {
    const params = new URLSearchParams({ page: String(page) });

    if (search.trim() !== '') {
        params.set('search', search.trim());
    }

    return queryOptions({
        queryKey: ['master-drugs', search, page] as const,
        queryFn: () => apiGetPaginated<MasterDrug>(`/api/master-drugs?${params.toString()}`),
    });
}

export function productsQueryOptions(page = 1) {
    return queryOptions({
        queryKey: ['products', page] as const,
        queryFn: () => apiGetPaginated<PharmacyProduct>(`/api/products?page=${page}`),
    });
}

export function productQueryOptions(id: string) {
    return queryOptions({
        queryKey: ['products', id] as const,
        queryFn: () => apiGet<PharmacyProduct>(`/api/products/${id}`),
    });
}

export function createProduct(payload: ProductPayload): Promise<PharmacyProduct> {
    return apiSend<PharmacyProduct>('/api/products', 'POST', payload);
}

export function updateProduct(
    id: string,
    payload: Partial<ProductPayload>,
): Promise<PharmacyProduct> {
    return apiSend<PharmacyProduct>(`/api/products/${id}`, 'PATCH', payload);
}

export function createUnit(productId: string, payload: UnitPayload): Promise<ProductUnit> {
    return apiSend<ProductUnit>(`/api/products/${productId}/units`, 'POST', payload);
}

export function updateUnit(
    productId: string,
    unitId: string,
    payload: Partial<UnitPayload>,
): Promise<ProductUnit> {
    return apiSend<ProductUnit>(`/api/products/${productId}/units/${unitId}`, 'PATCH', payload);
}

export function upsertBranchSetting(
    productId: string,
    payload: BranchSettingPayload,
): Promise<ProductBranchSetting> {
    return apiSend<ProductBranchSetting>(
        `/api/products/${productId}/branch-settings`,
        'POST',
        payload,
    );
}
