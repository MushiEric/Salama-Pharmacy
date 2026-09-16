import { queryOptions } from '@tanstack/react-query';
import { apiGet, apiSend, ApiError } from '@/shared/api/client';

export type TenantSettings = {
    expiry_red_days: number;
    expiry_yellow_days: number;
    block_expired_sales: boolean;
    currency: string;
    timezone: string;
};

export type SubscriptionPackage = {
    id: string;
    name: string;
    slug: string;
    max_branches: number | null;
    max_users: number | null;
    max_devices: number | null;
    max_stock_items: number | null;
    max_transactions_per_period: number | null;
    features: string[] | Record<string, unknown> | null;
};

export type TenantSubscription = {
    id: string;
    tenant_id: string;
    status: string;
    is_read_only: boolean;
    trial_ends_at: string | null;
    current_period_ends_at: string | null;
    cancelled_at: string | null;
    package?: SubscriptionPackage | null;
};

export function tenantSettingsQueryOptions() {
    return queryOptions({
        queryKey: ['tenant-settings'] as const,
        queryFn: () => apiGet<TenantSettings>('/api/tenant/settings'),
    });
}

export function subscriptionQueryOptions() {
    return queryOptions({
        queryKey: ['subscription'] as const,
        queryFn: async (): Promise<TenantSubscription | null> => {
            try {
                return await apiGet<TenantSubscription>('/api/subscription');
            } catch (error) {
                if (error instanceof ApiError && error.status === 404) {
                    return null;
                }

                throw error;
            }
        },
    });
}

export function updateTenantSettings(payload: TenantSettings): Promise<TenantSettings> {
    return apiSend<TenantSettings>('/api/tenant/settings', 'PATCH', payload);
}
