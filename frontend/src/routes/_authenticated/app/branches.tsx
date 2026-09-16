import { createFileRoute, redirect } from '@tanstack/react-router';
import { userQueryOptions } from '@/features/auth/api';
import { BranchesPage } from '@/features/branches/branches-page';
import { can, Permission } from '@/shared/lib/permissions';

export const Route = createFileRoute('/_authenticated/app/branches')({
    beforeLoad: async ({ context }) => {
        const user = await context.queryClient.ensureQueryData(userQueryOptions());

        if (!can(user, Permission.BRANCH_VIEW)) {
            throw redirect({ to: '/app' });
        }
    },
    component: BranchesPage,
});
