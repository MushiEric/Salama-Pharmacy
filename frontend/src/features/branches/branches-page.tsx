import { useQuery } from '@tanstack/react-query';
import { useMemo, useState } from 'react';
import type { ColumnDef } from '@tanstack/react-table';
import { branchesQueryOptions, type Branch } from '@/features/branches/api';
import { BranchFormDialog } from '@/features/branches/branch-form-dialog';
import { userQueryOptions } from '@/features/auth/api';
import { DataTable } from '@/shared/components/data-table';
import { PageHeader } from '@/shared/components/page-header';
import { StatusBadge } from '@/shared/components/status-badge';
import { Button } from '@/shared/components/ui/button';
import { can, Permission } from '@/shared/lib/permissions';

export function BranchesPage() {
    const [page, setPage] = useState(1);
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<Branch | null>(null);
    const { data: user } = useQuery(userQueryOptions());
    const { data, isPending } = useQuery(branchesQueryOptions(page));
    const canManage = can(user, Permission.BRANCH_MANAGE);

    const columns = useMemo<ColumnDef<Branch, unknown>[]>(
        () => [
            { accessorKey: 'name', header: 'Name' },
            {
                accessorKey: 'phone',
                header: 'Phone',
                cell: ({ row }) => row.original.phone ?? '—',
            },
            {
                accessorKey: 'address',
                header: 'Address',
                cell: ({ row }) => row.original.address ?? '—',
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
                    canManage ? (
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
        [canManage],
    );

    return (
        <div>
            <PageHeader
                title="Branches"
                description="Each branch keeps its own stock, prices, and users."
                actions={
                    canManage ? (
                        <Button
                            type="button"
                            onClick={() => {
                                setSelected(null);
                                setOpen(true);
                            }}
                        >
                            New branch
                        </Button>
                    ) : null
                }
            />
            {isPending ? <p className="text-muted-foreground text-sm">Loading branches…</p> : null}
            {data ? (
                <DataTable
                    columns={columns}
                    data={data.data}
                    page={data.meta.current_page}
                    lastPage={data.meta.last_page}
                    onPageChange={setPage}
                />
            ) : null}
            <BranchFormDialog open={open} branch={selected} onOpenChange={setOpen} />
        </div>
    );
}
