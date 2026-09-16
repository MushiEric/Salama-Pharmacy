import { createFileRoute } from '@tanstack/react-router';
import { useQuery } from '@tanstack/react-query';
import { userQueryOptions } from '@/features/auth/api';
import { formatLocalDateTime } from '@/shared/lib/datetime';
import { isTenantUser } from '@/shared/lib/permissions';

export const Route = createFileRoute('/_authenticated/app/')({
    component: DashboardPage,
});

function DashboardPage() {
    const { data: user } = useQuery(userQueryOptions());

    return (
        <div className="grid max-w-2xl gap-2">
            <h1 className="text-2xl font-semibold">Dashboard</h1>
            {isTenantUser(user) ? (
                <p className="text-muted-foreground">
                    Signed in as {user?.name}. Tenant context is resolved by the API from your
                    session, not from the client.
                </p>
            ) : (
                <p className="text-muted-foreground">
                    This SPA is the tenant-facing pharmacy app. Platform administration (tenants and
                    packages) is a separate concern and is not included here.
                </p>
            )}
            <p className="text-muted-foreground text-sm">
                Local time: {formatLocalDateTime(new Date())}
            </p>
        </div>
    );
}
