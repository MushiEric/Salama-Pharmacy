import { queryOptions } from '@tanstack/react-query';
import { apiGet, apiGetPaginated, apiSend } from '@/shared/api/client';

export type RoleSummary = {
    id: string;
    name: string;
    slug: string;
    is_system: boolean;
};

export type PermissionItem = {
    id: string;
    code: string;
    description: string | null;
};

export type Role = RoleSummary & {
    tenant_id: string | null;
    permissions?: PermissionItem[];
};

export type TenantUser = {
    id: string;
    tenant_id: string;
    branch_id: string | null;
    name: string;
    email: string;
    phone: string | null;
    status: 'active' | 'inactive' | 'suspended';
    roles?: RoleSummary[];
};

export type UserPayload = {
    name: string;
    email: string;
    phone?: string | null;
    password?: string;
    branch_id?: string | null;
    status?: TenantUser['status'];
    role_ids?: string[];
};

export type RolePayload = {
    name: string;
    permission_codes?: string[];
};

export function usersQueryOptions(page = 1) {
    return queryOptions({
        queryKey: ['users', page] as const,
        queryFn: () => apiGetPaginated<TenantUser>(`/api/users?page=${page}`),
    });
}

export function rolesQueryOptions(page = 1) {
    return queryOptions({
        queryKey: ['roles', page] as const,
        queryFn: () => apiGetPaginated<Role>(`/api/roles?page=${page}`),
    });
}

export function permissionsQueryOptions() {
    return queryOptions({
        queryKey: ['permissions'] as const,
        queryFn: () => apiGet<PermissionItem[]>('/api/permissions'),
    });
}

export function createUser(payload: UserPayload): Promise<TenantUser> {
    return apiSend<TenantUser>('/api/users', 'POST', payload);
}

export function updateUser(id: string, payload: UserPayload): Promise<TenantUser> {
    return apiSend<TenantUser>(`/api/users/${id}`, 'PATCH', payload);
}

export function createRole(payload: RolePayload): Promise<Role> {
    return apiSend<Role>('/api/roles', 'POST', payload);
}

export function updateRole(id: string, payload: RolePayload): Promise<Role> {
    return apiSend<Role>(`/api/roles/${id}`, 'PATCH', payload);
}

export function deleteRole(id: string): Promise<null> {
    return apiSend<null>(`/api/roles/${id}`, 'DELETE');
}
