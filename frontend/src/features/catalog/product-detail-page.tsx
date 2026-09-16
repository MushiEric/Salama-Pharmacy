import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Link } from '@tanstack/react-router';
import { useState } from 'react';
import { toast } from 'sonner';
import { userQueryOptions } from '@/features/auth/api';
import { branchesQueryOptions } from '@/features/branches/api';
import {
    createUnit,
    productQueryOptions,
    updateProduct,
    updateUnit,
    upsertBranchSetting,
    type PharmacyProduct,
} from '@/features/catalog/api';
import { ApiError } from '@/shared/api/client';
import { PageHeader } from '@/shared/components/page-header';
import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { NativeSelect } from '@/shared/components/ui/native-select';
import { formatTzs } from '@/shared/lib/datetime';
import { can, Permission } from '@/shared/lib/permissions';

type ProductDetailPageProps = {
    productId: string;
};

export function ProductDetailPage({ productId }: ProductDetailPageProps) {
    const { data: user } = useQuery(userQueryOptions());
    const { data: product, isPending } = useQuery(productQueryOptions(productId));
    const canUpdate = can(user, Permission.PRODUCT_UPDATE);
    const canPrice = can(user, Permission.PRODUCT_PRICE_UPDATE);

    if (isPending) {
        return <p className="text-muted-foreground text-sm">Loading product…</p>;
    }

    if (!product) {
        return <p className="text-muted-foreground text-sm">Product not found.</p>;
    }

    return (
        <div className="grid gap-6">
            <PageHeader
                title={product.local_name}
                description="Units convert to a single base unit. Branch prices are stored separately."
                actions={
                    <Link
                        to="/app/products"
                        className="inline-flex h-9 items-center rounded-md border px-4 text-sm"
                    >
                        Back to products
                    </Link>
                }
            />
            <ProductDetailsForm key={product.id} product={product} canUpdate={canUpdate} />
            <UnitsCard product={product} canUpdate={canUpdate} />
            <PricingCard product={product} canPrice={canPrice} />
        </div>
    );
}

function ProductDetailsForm({
    product,
    canUpdate,
}: {
    product: PharmacyProduct;
    canUpdate: boolean;
}) {
    const queryClient = useQueryClient();
    const [name, setName] = useState(product.local_name);
    const [prescription, setPrescription] = useState(product.prescription_required);
    const [status, setStatus] = useState(product.status);

    const mutation = useMutation({
        mutationFn: () =>
            updateProduct(product.id, {
                local_name: name,
                prescription_required: prescription,
                status,
            }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['products', product.id] });
            await queryClient.invalidateQueries({ queryKey: ['products'] });
            toast.success('Product updated');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to update product.');
        },
    });

    return (
        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <form
                    className="grid max-w-xl gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        mutation.mutate();
                    }}
                >
                    <div className="grid gap-2">
                        <Label htmlFor="detail-name">Local name</Label>
                        <Input
                            id="detail-name"
                            value={name}
                            disabled={!canUpdate}
                            onChange={(event) => setName(event.target.value)}
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={prescription}
                            disabled={!canUpdate}
                            onChange={(event) => setPrescription(event.target.checked)}
                        />
                        Prescription required
                    </label>
                    <div className="grid gap-2">
                        <Label htmlFor="detail-status">Status</Label>
                        <NativeSelect
                            id="detail-status"
                            value={status}
                            disabled={!canUpdate}
                            onChange={(event) =>
                                setStatus(event.target.value as PharmacyProduct['status'])
                            }
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </NativeSelect>
                    </div>
                    {canUpdate ? (
                        <div>
                            <Button type="submit" disabled={mutation.isPending}>
                                {mutation.isPending ? 'Saving…' : 'Save details'}
                            </Button>
                        </div>
                    ) : null}
                </form>
            </CardContent>
        </Card>
    );
}

function UnitsCard({ product, canUpdate }: { product: PharmacyProduct; canUpdate: boolean }) {
    const queryClient = useQueryClient();
    const [name, setName] = useState('');
    const [symbol, setSymbol] = useState('');
    const [multiplier, setMultiplier] = useState('1');
    const [isBase, setIsBase] = useState(false);

    const add = useMutation({
        mutationFn: () =>
            createUnit(product.id, {
                name,
                symbol: symbol || null,
                multiplier_to_base: Number(multiplier),
                is_base: isBase,
            }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['products', product.id] });
            setName('');
            setSymbol('');
            setMultiplier('1');
            setIsBase(false);
            toast.success('Unit added');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to add unit.');
        },
    });

    const toggleBase = useMutation({
        mutationFn: (unitId: string) => updateUnit(product.id, unitId, { is_base: true }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['products', product.id] });
            toast.success('Base unit updated');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to update unit.');
        },
    });

    return (
        <Card>
            <CardHeader>
                <CardTitle>Units</CardTitle>
            </CardHeader>
            <CardContent className="grid gap-4">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="text-muted-foreground text-left">
                            <tr>
                                <th className="py-2">Name</th>
                                <th>Symbol</th>
                                <th>Multiplier to base</th>
                                <th>Base</th>
                            </tr>
                        </thead>
                        <tbody>
                            {(product.units ?? []).map((unit) => (
                                <tr key={unit.id} className="border-t">
                                    <td className="py-2">{unit.name}</td>
                                    <td>{unit.symbol ?? '—'}</td>
                                    <td>{unit.multiplier_to_base}</td>
                                    <td>
                                        {unit.is_base ? (
                                            <span className="text-xs font-medium">Base</span>
                                        ) : canUpdate ? (
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                onClick={() => toggleBase.mutate(unit.id)}
                                            >
                                                Make base
                                            </Button>
                                        ) : (
                                            '—'
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                {canUpdate ? (
                    <form
                        className="grid gap-3 md:grid-cols-4"
                        onSubmit={(event) => {
                            event.preventDefault();
                            add.mutate();
                        }}
                    >
                        <Input
                            placeholder="Name (tablet)"
                            value={name}
                            onChange={(event) => setName(event.target.value)}
                        />
                        <Input
                            placeholder="Symbol"
                            value={symbol}
                            onChange={(event) => setSymbol(event.target.value)}
                        />
                        <Input
                            placeholder="Multiplier"
                            type="number"
                            min="0"
                            step="any"
                            value={multiplier}
                            onChange={(event) => setMultiplier(event.target.value)}
                        />
                        <div className="flex items-center gap-3">
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={isBase}
                                    onChange={(event) => setIsBase(event.target.checked)}
                                />
                                Base
                            </label>
                            <Button type="submit" disabled={add.isPending || name.trim() === ''}>
                                Add
                            </Button>
                        </div>
                    </form>
                ) : null}
            </CardContent>
        </Card>
    );
}

function PricingCard({ product, canPrice }: { product: PharmacyProduct; canPrice: boolean }) {
    const queryClient = useQueryClient();
    const { data: branches } = useQuery(branchesQueryOptions(1));
    const [branchId, setBranchId] = useState('');
    const [price, setPrice] = useState('');
    const [reorder, setReorder] = useState('0');

    const save = useMutation({
        mutationFn: () =>
            upsertBranchSetting(product.id, {
                branch_id: branchId,
                selling_price: Number(price),
                reorder_level_base: Number(reorder),
                is_active: true,
            }),
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['products', product.id] });
            setPrice('');
            toast.success('Branch price saved');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to save price.');
        },
    });

    const branchName = (id: string) =>
        branches?.data.find((branch) => branch.id === id)?.name ?? id;

    return (
        <Card>
            <CardHeader>
                <CardTitle>Branch pricing</CardTitle>
            </CardHeader>
            <CardContent className="grid gap-4">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="text-muted-foreground text-left">
                            <tr>
                                <th className="py-2">Branch</th>
                                <th>Selling price</th>
                                <th>Reorder (base)</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            {(product.branch_settings ?? []).map((setting) => (
                                <tr key={setting.id} className="border-t">
                                    <td className="py-2">{branchName(setting.branch_id)}</td>
                                    <td>{formatTzs(setting.selling_price)}</td>
                                    <td>{setting.reorder_level_base}</td>
                                    <td>{setting.is_active ? 'Yes' : 'No'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                {canPrice ? (
                    <form
                        className="grid gap-3 md:grid-cols-4"
                        onSubmit={(event) => {
                            event.preventDefault();
                            save.mutate();
                        }}
                    >
                        <NativeSelect
                            value={branchId}
                            onChange={(event) => setBranchId(event.target.value)}
                        >
                            <option value="">Select branch</option>
                            {(branches?.data ?? []).map((branch) => (
                                <option key={branch.id} value={branch.id}>
                                    {branch.name}
                                </option>
                            ))}
                        </NativeSelect>
                        <Input
                            placeholder="Selling price"
                            type="number"
                            min="0"
                            step="0.01"
                            value={price}
                            onChange={(event) => setPrice(event.target.value)}
                        />
                        <Input
                            placeholder="Reorder level"
                            type="number"
                            min="0"
                            step="any"
                            value={reorder}
                            onChange={(event) => setReorder(event.target.value)}
                        />
                        <Button
                            type="submit"
                            disabled={save.isPending || branchId === '' || price === ''}
                        >
                            Save price
                        </Button>
                    </form>
                ) : null}
            </CardContent>
        </Card>
    );
}
