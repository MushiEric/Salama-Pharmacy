import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { toast } from 'sonner';
import { z } from 'zod';
import { branchesQueryOptions } from '@/features/branches/api';
import {
    createUser,
    rolesQueryOptions,
    updateUser,
    type TenantUser,
} from '@/features/identity/api';
import { ApiError, validationErrors } from '@/shared/api/client';
import { Button } from '@/shared/components/ui/button';
import { Dialog } from '@/shared/components/ui/dialog';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { NativeSelect } from '@/shared/components/ui/native-select';
import { can, Permission } from '@/shared/lib/permissions';
import { userQueryOptions } from '@/features/auth/api';

const schema = z.object({
    name: z.string().min(1),
    email: z.string().email(),
    phone: z.string(),
    password: z.string(),
    branch_id: z.string(),
    status: z.enum(['active', 'inactive', 'suspended']),
    role_ids: z.array(z.string()),
});

type Values = z.infer<typeof schema>;

type UserFormDialogProps = {
    open: boolean;
    user: TenantUser | null;
    onOpenChange: (open: boolean) => void;
};

export function UserFormDialog({ open, user, onOpenChange }: UserFormDialogProps) {
    const queryClient = useQueryClient();
    const { data: currentUser } = useQuery(userQueryOptions());
    const canAssignRoles = can(currentUser, Permission.ROLE_MANAGE);
    const { data: branches } = useQuery({ ...branchesQueryOptions(1), enabled: open });
    const { data: roles } = useQuery({ ...rolesQueryOptions(1), enabled: open && canAssignRoles });

    const form = useForm<Values>({
        resolver: zodResolver(schema),
        defaultValues: {
            name: '',
            email: '',
            phone: '',
            password: '',
            branch_id: '',
            status: 'active',
            role_ids: [],
        },
    });

    useEffect(() => {
        if (!open) {
            return;
        }

        form.reset({
            name: user?.name ?? '',
            email: user?.email ?? '',
            phone: user?.phone ?? '',
            password: '',
            branch_id: user?.branch_id ?? '',
            status: user?.status ?? 'active',
            role_ids: user?.roles?.map((role) => role.id) ?? [],
        });
    }, [open, user, form]);

    const mutation = useMutation({
        mutationFn: (values: Values) => {
            const payload = {
                name: values.name,
                email: values.email,
                phone: values.phone || null,
                branch_id: values.branch_id || null,
                status: values.status,
                role_ids: canAssignRoles ? values.role_ids : undefined,
                ...(values.password ? { password: values.password } : {}),
            };

            if (!user && !values.password) {
                throw new Error('Password is required for new users.');
            }

            return user
                ? updateUser(user.id, payload)
                : createUser({ ...payload, password: values.password });
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['users'] });
            toast.success(user ? 'User updated' : 'User created');
            onOpenChange(false);
        },
        onError: (error) => {
            const fields = validationErrors(error);
            Object.entries(fields).forEach(([name, message]) => {
                form.setError(name as keyof Values, { message });
            });
            toast.error(error instanceof Error ? error.message : 'Unable to save user.');
        },
    });

    return (
        <Dialog
            open={open}
            onOpenChange={onOpenChange}
            title={user ? 'Edit user' : 'Create user'}
            description="Operational users belong to one branch. Pharmacy Admin may omit a branch."
        >
            <form
                className="grid gap-4"
                onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
            >
                <div className="grid gap-2">
                    <Label htmlFor="user-name">Name</Label>
                    <Input id="user-name" {...form.register('name')} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="user-email">Email</Label>
                    <Input id="user-email" type="email" {...form.register('email')} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="user-phone">Phone</Label>
                    <Input id="user-phone" {...form.register('phone')} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="user-password">{user ? 'New password' : 'Password'}</Label>
                    <Input
                        id="user-password"
                        type="password"
                        autoComplete="new-password"
                        {...form.register('password')}
                    />
                    {user ? (
                        <p className="text-muted-foreground text-xs">
                            Leave blank to keep the current password.
                        </p>
                    ) : null}
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="user-branch">Branch</Label>
                    <NativeSelect id="user-branch" {...form.register('branch_id')}>
                        <option value="">None (tenant admin)</option>
                        {(branches?.data ?? []).map((branch) => (
                            <option key={branch.id} value={branch.id}>
                                {branch.name}
                            </option>
                        ))}
                    </NativeSelect>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="user-status">Status</Label>
                    <NativeSelect id="user-status" {...form.register('status')}>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </NativeSelect>
                </div>
                {canAssignRoles ? (
                    <fieldset className="grid gap-2">
                        <legend className="text-sm font-medium">Roles</legend>
                        <Controller
                            control={form.control}
                            name="role_ids"
                            render={({ field }) => (
                                <div className="grid max-h-40 gap-2 overflow-y-auto rounded-md border p-3">
                                    {(roles?.data ?? []).map((role) => (
                                        <label
                                            key={role.id}
                                            className="flex items-center gap-2 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={field.value.includes(role.id)}
                                                onChange={(event) => {
                                                    if (event.target.checked) {
                                                        field.onChange([...field.value, role.id]);
                                                    } else {
                                                        field.onChange(
                                                            field.value.filter(
                                                                (id) => id !== role.id,
                                                            ),
                                                        );
                                                    }
                                                }}
                                            />
                                            {role.name}
                                            {role.is_system ? (
                                                <span className="text-muted-foreground text-xs">
                                                    system
                                                </span>
                                            ) : null}
                                        </label>
                                    ))}
                                </div>
                            )}
                        />
                    </fieldset>
                ) : null}
                {form.formState.errors.password ? (
                    <p className="text-destructive text-sm">
                        {form.formState.errors.password.message}
                    </p>
                ) : null}
                {mutation.error instanceof ApiError ? (
                    <p className="text-destructive text-sm">{mutation.error.message}</p>
                ) : null}
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
