import { createFileRoute, redirect } from '@tanstack/react-router';
import { userQueryOptions } from '@/features/auth/api';
import { UsersAndRolesPage } from '@/features/identity/users-and-roles-page';
import { can, Permission } from '@/shared/lib/permissions';

export const Route = createFileRoute('/_authenticated/app/users')({
    beforeLoad: async ({ context }) => {
        const user = await context.queryClient.ensureQueryData(userQueryOptions());

        if (!can(user, Permission.USER_VIEW) && !can(user, Permission.ROLE_MANAGE)) {
            throw redirect({ to: '/app' });
        }
    },
    component: UsersAndRolesPage,
});
