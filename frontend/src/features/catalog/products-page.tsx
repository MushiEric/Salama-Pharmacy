import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useMemo, useState } from 'react';
import type { ColumnDef } from '@tanstack/react-table';
import { Link } from '@tanstack/react-router';
import { toast } from 'sonner';
import { userQueryOptions } from '@/features/auth/api';
import {
    createProduct,
    masterDrugsQueryOptions,
    productsQueryOptions,
    type MasterDrug,
    type PharmacyProduct,
} from '@/features/catalog/api';
import { ApiError } from '@/shared/api/client';
import { DataTable } from '@/shared/components/data-table';
import { PageHeader } from '@/shared/components/page-header';
import { StatusBadge } from '@/shared/components/status-badge';
import { TabButton } from '@/shared/components/tab-button';
import { Button } from '@/shared/components/ui/button';
import { Dialog } from '@/shared/components/ui/dialog';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { NativeSelect } from '@/shared/components/ui/native-select';
import { can, Permission } from '@/shared/lib/permissions';

export function ProductsPage() {
    const { data: user } = useQuery(userQueryOptions());
    const [tab, setTab] = useState<'products' | 'catalog'>('products');
    const canViewCatalog = can(user, Permission.DRUG_VIEW);

    return (
        <div>
            <PageHeader
                title="Product catalog"
                description="Browse the master catalog, then configure pharmacy products, units, and branch prices."
            />
            <div className="bg-muted mb-4 inline-flex rounded-lg p-1">
                <TabButton active={tab === 'products'} onClick={() => setTab('products')}>
                    Pharmacy products
                </TabButton>
                {canViewCatalog ? (
                    <TabButton active={tab === 'catalog'} onClick={() => setTab('catalog')}>
                        Master catalog
                    </TabButton>
                ) : null}
            </div>
            {tab === 'products' ? <ProductsPanel /> : <MasterCatalogPanel />}
        </div>
    );
}

function ProductsPanel() {
    const { data: user } = useQuery(userQueryOptions());
    const [page, setPage] = useState(1);
    const [open, setOpen] = useState(false);
    const { data, isPending } = useQuery(productsQueryOptions(page));
    const canCreate = can(user, Permission.PRODUCT_CREATE);

    const columns = useMemo<ColumnDef<PharmacyProduct, unknown>[]>(
        () => [
            { accessorKey: 'local_name', header: 'Name' },
            {
                id: 'prescription',
                header: 'Prescription',
                cell: ({ row }) => (row.original.prescription_required ? 'Required' : 'No'),
            },
            {
                accessorKey: 'status',
                header: 'Status',
                cell: ({ row }) => <StatusBadge status={row.original.status} />,
            },
            {
                id: 'units',
                header: 'Units',
                cell: ({ row }) => String(row.original.units?.length ?? 0),
            },
            {
                id: 'actions',
                header: '',
                cell: ({ row }) => (
                    <Link
                        to="/app/products/$productId"
                        params={{ productId: row.original.id }}
                        className="text-sm font-medium hover:underline"
                    >
                        Open
                    </Link>
                ),
            },
        ],
        [],
    );

    return (
        <div className="grid gap-3">
            {canCreate ? (
                <div className="flex justify-end">
                    <Button type="button" onClick={() => setOpen(true)}>
                        New local product
                    </Button>
                </div>
            ) : null}
            {isPending ? <p className="text-muted-foreground text-sm">Loading products…</p> : null}
            {data ? (
                <DataTable
                    columns={columns}
                    data={data.data}
                    page={data.meta.current_page}
                    lastPage={data.meta.last_page}
                    onPageChange={setPage}
                />
            ) : null}
            <CreateProductDialog open={open} onOpenChange={setOpen} />
        </div>
    );
}

function MasterCatalogPanel() {
    const { data: user } = useQuery(userQueryOptions());
    const [search, setSearch] = useState('');
    const [submitted, setSubmitted] = useState('');
    const [page, setPage] = useState(1);
    const { data, isPending } = useQuery(masterDrugsQueryOptions(submitted, page));
    const canCreate = can(user, Permission.PRODUCT_CREATE);
    const queryClient = useQueryClient();

    const add = useMutation({
        mutationFn: (drug: MasterDrug) =>
            createProduct({
                master_drug_id: drug.id,
                local_name: drug.brand_name,
            }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['products'] });
            toast.success('Product added to this pharmacy');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to create product.');
        },
    });

    const columns = useMemo<ColumnDef<MasterDrug, unknown>[]>(
        () => [
            { accessorKey: 'brand_name', header: 'Brand' },
            {
                accessorKey: 'generic_drug_name',
                header: 'Generic',
                cell: ({ row }) => row.original.generic_drug_name ?? '—',
            },
            { accessorKey: 'strength', header: 'Strength' },
            { accessorKey: 'dosage_form', header: 'Form' },
            {
                id: 'actions',
                header: '',
                cell: ({ row }) =>
                    canCreate ? (
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            disabled={add.isPending}
                            onClick={() => add.mutate(row.original)}
                        >
                            Add to pharmacy
                        </Button>
                    ) : null,
            },
        ],
        [add, canCreate],
    );

    return (
        <div className="grid gap-3">
            <form
                className="flex max-w-lg gap-2"
                onSubmit={(event) => {
                    event.preventDefault();
                    setPage(1);
                    setSubmitted(search);
                }}
            >
                <Input
                    value={search}
                    placeholder="Search brand name"
                    onChange={(event) => setSearch(event.target.value)}
                />
                <Button type="submit" variant="outline">
                    Search
                </Button>
            </form>
            {isPending ? <p className="text-muted-foreground text-sm">Loading catalog…</p> : null}
            {data ? (
                <DataTable
                    columns={columns}
                    data={data.data}
                    empty="No master drugs match that search."
                    page={data.meta.current_page}
                    lastPage={data.meta.last_page}
                    onPageChange={setPage}
                />
            ) : null}
        </div>
    );
}

function CreateProductDialog({
    open,
    onOpenChange,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const queryClient = useQueryClient();
    const [name, setName] = useState('');
    const [prescription, setPrescription] = useState(false);
    const [status, setStatus] = useState<'active' | 'inactive'>('active');

    useEffect(() => {
        if (open) {
            setName('');
            setPrescription(false);
            setStatus('active');
        }
    }, [open]);

    const mutation = useMutation({
        mutationFn: () =>
            createProduct({
                local_name: name,
                prescription_required: prescription,
                status,
            }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['products'] });
            toast.success('Product created');
            onOpenChange(false);
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to create product.');
        },
    });

    return (
        <Dialog
            open={open}
            onOpenChange={onOpenChange}
            title="New local product"
            description="Use this when there is no master-drug match. You can still attach units and branch prices next."
        >
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    mutation.mutate();
                }}
            >
                <div className="grid gap-2">
                    <Label htmlFor="product-name">Local name</Label>
                    <Input
                        id="product-name"
                        value={name}
                        onChange={(event) => setName(event.target.value)}
                    />
                </div>
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={prescription}
                        onChange={(event) => setPrescription(event.target.checked)}
                    />
                    Prescription required
                </label>
                <div className="grid gap-2">
                    <Label htmlFor="product-status">Status</Label>
                    <NativeSelect
                        id="product-status"
                        value={status}
                        onChange={(event) => setStatus(event.target.value as 'active' | 'inactive')}
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </NativeSelect>
                </div>
                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button type="submit" disabled={mutation.isPending || name.trim() === ''}>
                        {mutation.isPending ? 'Saving…' : 'Create'}
                    </Button>
                </div>
            </form>
        </Dialog>
    );
}
