import type { AuthenticatedUser } from '@/shared/types/auth';

export const Permission = {
    BRANCH_VIEW: 'branch.view',
    BRANCH_MANAGE: 'branch.manage',
    USER_VIEW: 'user.view',
    USER_CREATE: 'user.create',
    USER_UPDATE: 'user.update',
    ROLE_MANAGE: 'role.manage',
    DRUG_VIEW: 'drug.view',
    PRODUCT_CREATE: 'product.create',
    PRODUCT_UPDATE: 'product.update',
    PRODUCT_PRICE_UPDATE: 'product.price.update',
    SETTINGS_MANAGE: 'settings.manage',
    SUBSCRIPTION_VIEW: 'subscription.view',
} as const;

export function isTenantUser(user: AuthenticatedUser | null | undefined): boolean {
    return Boolean(user?.tenant_id);
}

export function can(user: AuthenticatedUser | null | undefined, permission: string): boolean {
    if (!user || !isTenantUser(user)) {
        return false;
    }

    if (user.permissions.includes('*') || user.is_pharmacy_admin) {
        return true;
    }

    return user.permissions.includes(permission);
}
