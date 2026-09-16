import { queryOptions } from '@tanstack/react-query';
import { apiGet, apiGetPaginated, apiSend } from '@/shared/api/client';

export type Branch = {
    id: string;
    tenant_id: string;
    name: string;
    phone: string | null;
    address: string | null;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
};

export type BranchPayload = {
    name: string;
    phone?: string | null;
    address?: string | null;
    status?: 'active' | 'inactive';
};

export function branchesQueryOptions(page = 1) {
    return queryOptions({
        queryKey: ['branches', page] as const,
        queryFn: () => apiGetPaginated<Branch>(`/api/branches?page=${page}`),
    });
}

export function createBranch(payload: BranchPayload): Promise<Branch> {
    return apiSend<Branch>('/api/branches', 'POST', payload);
}

export function updateBranch(id: string, payload: BranchPayload): Promise<Branch> {
    return apiSend<Branch>(`/api/branches/${id}`, 'PATCH', payload);
}

export function getBranch(id: string): Promise<Branch> {
    return apiGet<Branch>(`/api/branches/${id}`);
}
