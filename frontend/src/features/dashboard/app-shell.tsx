import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Link, Outlet, useNavigate } from '@tanstack/react-router';
import { logout, userQueryOptions } from '@/features/auth/api';
import { Button } from '@/shared/components/ui/button';
import { can, isTenantUser, Permission } from '@/shared/lib/permissions';
import { useUiStore } from '@/shared/stores/ui-store';
import { cn } from '@/shared/lib/utils';

const nav = [
    { to: '/app', label: 'Dashboard', exact: true },
    { to: '/app/branches', label: 'Branches', permission: Permission.BRANCH_VIEW },
    { to: '/app/users', label: 'Users & roles', permission: Permission.USER_VIEW },
    { to: '/app/products', label: 'Products', permission: Permission.DRUG_VIEW },
    { to: '/app/settings', label: 'Settings', permission: Permission.SETTINGS_MANAGE },
] as const;

export function AppShell() {
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const { data: user } = useQuery(userQueryOptions());
    const sidebarOpen = useUiStore((state) => state.sidebarOpen);
    const setSidebarOpen = useUiStore((state) => state.setSidebarOpen);

    const logoutMutation = useMutation({
        mutationFn: logout,
        onSuccess: async () => {
            queryClient.setQueryData(userQueryOptions().queryKey, null);
            await navigate({ to: '/login' });
        },
    });

    const items = nav.filter((item) => {
        if (!('permission' in item)) {
            return true;
        }

        if (item.to === '/app/users') {
            return can(user, Permission.USER_VIEW) || can(user, Permission.ROLE_MANAGE);
        }

        if (item.to === '/app/settings') {
            return can(user, Permission.SETTINGS_MANAGE) || can(user, Permission.SUBSCRIPTION_VIEW);
        }

        return can(user, item.permission);
    });

    return (
        <div className="bg-muted/40 flex min-h-screen">
            {sidebarOpen ? (
                <aside className="bg-background flex w-56 flex-col border-r p-4">
                    <p className="mb-6 text-sm font-semibold">SalamaPharma</p>
                    <nav className="grid gap-1 text-sm">
                        {items.map((item) => (
                            <Link
                                key={item.to}
                                to={item.to}
                                activeOptions={{ exact: 'exact' in item && item.exact }}
                                className="hover:bg-accent rounded-md px-2 py-1.5"
                                activeProps={{ className: 'rounded-md bg-accent px-2 py-1.5' }}
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>
                </aside>
            ) : null}
            <div className="flex min-w-0 flex-1 flex-col">
                <header className="bg-background flex items-center justify-between border-b px-4 py-3">
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                    >
                        Menu
                    </Button>
                    <div className="flex items-center gap-3 text-sm">
                        <span className={cn('text-muted-foreground')}>{user?.email}</span>
                        {user && !isTenantUser(user) ? (
                            <span className="text-muted-foreground text-xs">Platform</span>
                        ) : null}
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            disabled={logoutMutation.isPending}
                            onClick={() => logoutMutation.mutate()}
                        >
                            Sign out
                        </Button>
                    </div>
                </header>
                <main className="flex-1 p-6">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
