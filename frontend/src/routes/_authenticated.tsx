import { createFileRoute, redirect } from '@tanstack/react-router';
import { userQueryOptions } from '@/features/auth/api';
import { AppShell } from '@/features/dashboard/app-shell';

export const Route = createFileRoute('/_authenticated')({
    beforeLoad: async ({ context, location }) => {
        const user = await context.queryClient.ensureQueryData(userQueryOptions());

        if (!user) {
            throw redirect({
                to: '/login',
                search: { redirect: location.href },
            });
        }
    },
    component: AppShell,
});
