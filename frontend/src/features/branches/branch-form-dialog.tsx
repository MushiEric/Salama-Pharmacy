import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';
import { z } from 'zod';
import { createBranch, updateBranch, type Branch } from '@/features/branches/api';
import { ApiError, validationErrors } from '@/shared/api/client';
import { Button } from '@/shared/components/ui/button';
import { Dialog } from '@/shared/components/ui/dialog';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { NativeSelect } from '@/shared/components/ui/native-select';

const schema = z.object({
    name: z.string().min(1, 'Name is required'),
    phone: z.string(),
    address: z.string(),
    status: z.enum(['active', 'inactive']),
});

type Values = z.infer<typeof schema>;

type BranchFormDialogProps = {
    open: boolean;
    branch: Branch | null;
    onOpenChange: (open: boolean) => void;
};

export function BranchFormDialog({ open, branch, onOpenChange }: BranchFormDialogProps) {
    const queryClient = useQueryClient();
    const form = useForm<Values>({
        resolver: zodResolver(schema),
        defaultValues: { name: '', phone: '', address: '', status: 'active' },
    });

    useEffect(() => {
        if (!open) {
            return;
        }

        form.reset({
            name: branch?.name ?? '',
            phone: branch?.phone ?? '',
            address: branch?.address ?? '',
            status: branch?.status ?? 'active',
        });
    }, [open, branch, form]);

    const mutation = useMutation({
        mutationFn: (values: Values) => {
            const payload = {
                name: values.name,
                phone: values.phone || null,
                address: values.address || null,
                status: values.status,
            };

            return branch ? updateBranch(branch.id, payload) : createBranch(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['branches'] });
            toast.success(branch ? 'Branch updated' : 'Branch created');
            onOpenChange(false);
        },
        onError: (error) => {
            const fields = validationErrors(error);
            Object.entries(fields).forEach(([name, message]) => {
                form.setError(name as keyof Values, { message });
            });
            toast.error(error instanceof ApiError ? error.message : 'Unable to save branch.');
        },
    });

    return (
        <Dialog
            open={open}
            onOpenChange={onOpenChange}
            title={branch ? 'Edit branch' : 'Create branch'}
            description="Inventory, prices, and users stay scoped to this branch."
        >
            <form
                className="grid gap-4"
                onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
            >
                <div className="grid gap-2">
                    <Label htmlFor="branch-name">Name</Label>
                    <Input id="branch-name" {...form.register('name')} />
                    {form.formState.errors.name ? (
                        <p className="text-destructive text-sm">
                            {form.formState.errors.name.message}
                        </p>
                    ) : null}
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="branch-phone">Phone</Label>
                    <Input id="branch-phone" {...form.register('phone')} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="branch-address">Address</Label>
                    <Input id="branch-address" {...form.register('address')} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="branch-status">Status</Label>
                    <NativeSelect id="branch-status" {...form.register('status')}>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </NativeSelect>
                </div>
                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button type="submit" disabled={mutation.isPending}>
                        {mutation.isPending ? 'Saving…' : 'Save'}
                    </Button>
                </div>
            </form>
        </Dialog>
    );
}
