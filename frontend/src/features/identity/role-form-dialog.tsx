import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useMemo, useState } from 'react';
import { toast } from 'sonner';
import {
    createRole,
    permissionsQueryOptions,
    updateRole,
    type Role,
} from '@/features/identity/api';
import { ApiError } from '@/shared/api/client';
import { Button } from '@/shared/components/ui/button';
import { Dialog } from '@/shared/components/ui/dialog';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';

type RoleFormDialogProps = {
    open: boolean;
    role: Role | null;
    onOpenChange: (open: boolean) => void;
};

function groupPermissions(codes: Array<{ code: string; description: string | null }>) {
    const groups = new Map<string, typeof codes>();

    for (const permission of codes) {
        const group = permission.code.split('.')[0] ?? 'other';
        const list = groups.get(group) ?? [];
        list.push(permission);
        groups.set(group, list);
    }

    return [...groups.entries()];
}

export function RoleFormDialog({ open, role, onOpenChange }: RoleFormDialogProps) {
    const queryClient = useQueryClient();
    const { data: permissions = [] } = useQuery({ ...permissionsQueryOptions(), enabled: open });
    const [name, setName] = useState('');
    const [selected, setSelected] = useState<string[]>([]);
    const groups = useMemo(() => groupPermissions(permissions), [permissions]);
    const locked = Boolean(role?.is_system);

    useEffect(() => {
        if (!open) {
            return;
        }

        setName(role?.name ?? '');
        setSelected(role?.permissions?.map((permission) => permission.code) ?? []);
    }, [open, role]);

    const mutation = useMutation({
        mutationFn: () => {
            const payload = { name, permission_codes: selected };

            return role ? updateRole(role.id, payload) : createRole(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['roles'] });
            toast.success(role ? 'Role updated' : 'Role created');
            onOpenChange(false);
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to save role.');
        },
    });

    return (
        <Dialog
            open={open}
            onOpenChange={onOpenChange}
            title={role ? 'Edit role' : 'Create role'}
            description={
                locked
                    ? 'Pharmacy Admin is a fixed role. Permissions for it are not editable.'
                    : 'Operational role names are yours. Authorization is permission-driven.'
            }
            className="max-w-2xl"
        >
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    mutation.mutate();
                }}
            >
                <div className="grid gap-2">
                    <Label htmlFor="role-name">Name</Label>
                    <Input
                        id="role-name"
                        value={name}
                        disabled={locked}
                        onChange={(event) => setName(event.target.value)}
                    />
                </div>
                <div className="grid max-h-80 gap-4 overflow-y-auto rounded-md border p-3">
                    {groups.map(([group, items]) => (
                        <fieldset key={group} className="grid gap-2">
                            <legend className="text-sm font-medium capitalize">{group}</legend>
                            {items.map((permission) => (
                                <label
                                    key={permission.code}
                                    className="flex items-start gap-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        className="mt-1"
                                        disabled={locked}
                                        checked={selected.includes(permission.code)}
                                        onChange={(event) => {
                                            setSelected((current) =>
                                                event.target.checked
                                                    ? [...current, permission.code]
                                                    : current.filter(
                                                          (code) => code !== permission.code,
                                                      ),
                                            );
                                        }}
                                    />
                                    <span>
                                        <span className="font-mono text-xs">{permission.code}</span>
                                        {permission.description ? (
                                            <span className="text-muted-foreground block">
                                                {permission.description}
                                            </span>
                                        ) : null}
                                    </span>
                                </label>
                            ))}
                        </fieldset>
                    ))}
                </div>
                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        disabled={mutation.isPending || locked || name.trim() === ''}
                    >
                        {mutation.isPending ? 'Saving…' : 'Save'}
                    </Button>
                </div>
            </form>
        </Dialog>
    );
}
