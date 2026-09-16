import { createFileRoute, redirect } from '@tanstack/react-router';
import { userQueryOptions } from '@/features/auth/api';
import { SettingsPage } from '@/features/settings/settings-page';
import { can, Permission } from '@/shared/lib/permissions';

export const Route = createFileRoute('/_authenticated/app/settings')({
    beforeLoad: async ({ context }) => {
        const user = await context.queryClient.ensureQueryData(userQueryOptions());

        if (!can(user, Permission.SETTINGS_MANAGE) && !can(user, Permission.SUBSCRIPTION_VIEW)) {
            throw redirect({ to: '/app' });
        }
    },
    component: SettingsPage,
});
