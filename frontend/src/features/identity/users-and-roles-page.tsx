import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useMemo, useState } from 'react';
import type { ColumnDef } from '@tanstack/react-table';
import { toast } from 'sonner';
import { userQueryOptions } from '@/features/auth/api';
import {
    deleteRole,
    rolesQueryOptions,
    usersQueryOptions,
    type Role,
    type TenantUser,
} from '@/features/identity/api';
import { RoleFormDialog } from '@/features/identity/role-form-dialog';
import { UserFormDialog } from '@/features/identity/user-form-dialog';
import { DataTable } from '@/shared/components/data-table';
import { PageHeader } from '@/shared/components/page-header';
import { StatusBadge } from '@/shared/components/status-badge';
import { TabButton } from '@/shared/components/tab-button';
import { Button } from '@/shared/components/ui/button';
import { ApiError } from '@/shared/api/client';
import { can, Permission } from '@/shared/lib/permissions';

export function UsersAndRolesPage() {
    const { data: currentUser } = useQuery(userQueryOptions());
    const canViewUsers = can(currentUser, Permission.USER_VIEW);
    const canManageRoles = can(currentUser, Permission.ROLE_MANAGE);
    const [tab, setTab] = useState<'users' | 'roles'>(canViewUsers ? 'users' : 'roles');
    const canCreateUsers = can(currentUser, Permission.USER_CREATE);
    const canUpdateUsers = can(currentUser, Permission.USER_UPDATE);

    return (
        <div>
            <PageHeader
                title="Users & roles"
                description="Pharmacy Admin creates operational roles. Only Platform Superadmin and Pharmacy Admin are fixed."
            />
            <div className="bg-muted mb-4 inline-flex rounded-lg p-1">
                {canViewUsers ? (
                    <TabButton active={tab === 'users'} onClick={() => setTab('users')}>
                        Users
                    </TabButton>
                ) : null}
                {canManageRoles ? (
                    <TabButton active={tab === 'roles'} onClick={() => setTab('roles')}>
                        Roles
                    </TabButton>
                ) : null}
            </div>
            {tab === 'users' && canViewUsers ? (
                <UsersPanel canCreate={canCreateUsers} canUpdate={canUpdateUsers} />
            ) : null}
            {tab === 'roles' && canManageRoles ? <RolesPanel /> : null}
        </div>
    );
}

function UsersPanel({ canCreate, canUpdate }: { canCreate: boolean; canUpdate: boolean }) {
    const [page, setPage] = useState(1);
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<TenantUser | null>(null);
    const { data, isPending } = useQuery(usersQueryOptions(page));

    const columns = useMemo<ColumnDef<TenantUser, unknown>[]>(
        () => [
            { accessorKey: 'name', header: 'Name' },
            { accessorKey: 'email', header: 'Email' },
            {
                id: 'roles',
                header: 'Roles',
                cell: ({ row }) => row.original.roles?.map((role) => role.name).join(', ') || '—',
            },
            {
                accessorKey: 'status',
                header: 'Status',
                cell: ({ row }) => <StatusBadge status={row.original.status} />,
            },
            {
                id: 'actions',
                header: '',
                cell: ({ row }) =>
                    canUpdate ? (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => {
                                setSelected(row.original);
                                setOpen(true);
                            }}
                        >
                            Edit
                        </Button>
                    ) : null,
            },
        ],
        [canUpdate],
    );

    return (
        <div className="grid gap-3">
            {canCreate ? (
                <div className="flex justify-end">
                    <Button
                        type="button"
                        onClick={() => {
                            setSelected(null);
                            setOpen(true);
                        }}
                    >
                        New user
                    </Button>
                </div>
            ) : null}
            {isPending ? <p className="text-muted-foreground text-sm">Loading users…</p> : null}
            {data ? (
                <DataTable
                    columns={columns}
                    data={data.data}
                    page={data.meta.current_page}
                    lastPage={data.meta.last_page}
                    onPageChange={setPage}
                />
            ) : null}
            <UserFormDialog open={open} user={selected} onOpenChange={setOpen} />
        </div>
    );
}

function RolesPanel() {
    const queryClient = useQueryClient();
    const [page, setPage] = useState(1);
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<Role | null>(null);
    const { data, isPending } = useQuery(rolesQueryOptions(page));

    const remove = useMutation({
        mutationFn: deleteRole,
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['roles'] });
            toast.success('Role deleted');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to delete role.');
        },
    });

    const columns = useMemo<ColumnDef<Role, unknown>[]>(
        () => [
            { accessorKey: 'name', header: 'Name' },
            { accessorKey: 'slug', header: 'Slug' },
            {
                id: 'permissions',
                header: 'Permissions',
                cell: ({ row }) => String(row.original.permissions?.length ?? 0),
            },
            {
                id: 'system',
                header: 'Type',
                cell: ({ row }) => (row.original.is_system ? 'Fixed' : 'Dynamic'),
            },
            {
                id: 'actions',
                header: '',
                cell: ({ row }) => (
                    <div className="flex justify-end gap-1">
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => {
                                setSelected(row.original);
                                setOpen(true);
                            }}
                        >
                            {row.original.is_system ? 'View' : 'Edit'}
                        </Button>
                        {row.original.is_system ? null : (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                disabled={remove.isPending}
                                onClick={() => {
                                    if (window.confirm(`Delete role “${row.original.name}”?`)) {
                                        remove.mutate(row.original.id);
                                    }
                                }}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                ),
            },
        ],
        [remove],
    );

    return (
        <div className="grid gap-3">
            <div className="flex justify-end">
                <Button
                    type="button"
                    onClick={() => {
                        setSelected(null);
                        setOpen(true);
                    }}
                >
                    New role
                </Button>
            </div>
            {isPending ? <p className="text-muted-foreground text-sm">Loading roles…</p> : null}
            {data ? (
                <DataTable
                    columns={columns}
                    data={data.data}
                    page={data.meta.current_page}
                    lastPage={data.meta.last_page}
                    onPageChange={setPage}
                />
            ) : null}
            <RoleFormDialog open={open} role={selected} onOpenChange={setOpen} />
        </div>
    );
}
