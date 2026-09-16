import { describe, expect, it } from 'vitest';
import { can, isTenantUser, Permission } from './permissions';
import type { AuthenticatedUser } from '@/shared/types/auth';

function user(overrides: Partial<AuthenticatedUser>): AuthenticatedUser {
    return {
        id: '1',
        name: 'Ada',
        email: 'ada@pharmacy.test',
        phone: null,
        status: 'active',
        tenant_id: 'tenant-1',
        branch_id: null,
        is_platform_superadmin: false,
        is_pharmacy_admin: false,
        permissions: [],
        roles: [],
        ...overrides,
    };
}

describe('permissions', () => {
    it('denies platform users tenant screens', () => {
        const platform = user({
            tenant_id: null,
            is_platform_superadmin: true,
            permissions: ['*'],
        });

        expect(isTenantUser(platform)).toBe(false);
        expect(can(platform, Permission.BRANCH_VIEW)).toBe(false);
    });

    it('grants pharmacy admin every tenant permission', () => {
        const admin = user({ is_pharmacy_admin: true });

        expect(can(admin, Permission.ROLE_MANAGE)).toBe(true);
    });

    it('checks the permission list for operational users', () => {
        const cashier = user({ permissions: [Permission.BRANCH_VIEW] });

        expect(can(cashier, Permission.BRANCH_VIEW)).toBe(true);
        expect(can(cashier, Permission.BRANCH_MANAGE)).toBe(false);
    });
});
