export type AuthenticatedUser = {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    tenant_id: string | null;
    branch_id: string | null;
    is_platform_superadmin: boolean;
    is_pharmacy_admin: boolean;
    permissions: string[];
    roles: Array<{
        id: string;
        name: string;
        slug: string;
        is_system: boolean;
    }>;
};
