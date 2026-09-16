import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { userQueryOptions } from '@/features/auth/api';
import {
    subscriptionQueryOptions,
    tenantSettingsQueryOptions,
    updateTenantSettings,
    type TenantSettings,
} from '@/features/settings/api';
import { ApiError } from '@/shared/api/client';
import { PageHeader } from '@/shared/components/page-header';
import { StatusBadge } from '@/shared/components/status-badge';
import { Button } from '@/shared/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/shared/components/ui/card';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { formatLocalDateTime } from '@/shared/lib/datetime';
import { can, Permission } from '@/shared/lib/permissions';

export function SettingsPage() {
    const { data: user } = useQuery(userQueryOptions());
    const showSettings = can(user, Permission.SETTINGS_MANAGE);
    const showSubscription = can(user, Permission.SUBSCRIPTION_VIEW);

    return (
        <div>
            <PageHeader
                title="Settings"
                description="Expiry thresholds and locale stay on the tenant. Subscription is read-only here."
            />
            <div className="grid gap-6 lg:grid-cols-2">
                {showSettings ? <SettingsForm /> : null}
                {showSubscription ? <SubscriptionCard /> : null}
            </div>
        </div>
    );
}

function SettingsForm() {
    const queryClient = useQueryClient();
    const { data } = useQuery(tenantSettingsQueryOptions());
    const [form, setForm] = useState<TenantSettings | null>(null);

    useEffect(() => {
        if (data) {
            setForm(data);
        }
    }, [data]);

    const mutation = useMutation({
        mutationFn: () => {
            if (!form) {
                throw new Error('Settings are still loading.');
            }

            return updateTenantSettings(form);
        },
        onSuccess: async (saved) => {
            queryClient.setQueryData(tenantSettingsQueryOptions().queryKey, saved);
            toast.success('Settings saved');
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.message : 'Unable to save settings.');
        },
    });

    if (!form) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Pharmacy settings</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-muted-foreground text-sm">Loading settings…</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Pharmacy settings</CardTitle>
                <CardDescription>
                    Red/yellow expiry windows, expired-sale blocking, currency, and timezone.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        mutation.mutate();
                    }}
                >
                    <div className="grid gap-2">
                        <Label htmlFor="expiry-red">Expiry red days</Label>
                        <Input
                            id="expiry-red"
                            type="number"
                            min="0"
                            value={form.expiry_red_days}
                            onChange={(event) =>
                                setForm({ ...form, expiry_red_days: Number(event.target.value) })
                            }
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="expiry-yellow">Expiry yellow days</Label>
                        <Input
                            id="expiry-yellow"
                            type="number"
                            min="0"
                            value={form.expiry_yellow_days}
                            onChange={(event) =>
                                setForm({ ...form, expiry_yellow_days: Number(event.target.value) })
                            }
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.block_expired_sales}
                            onChange={(event) =>
                                setForm({ ...form, block_expired_sales: event.target.checked })
                            }
                        />
                        Block sales of expired batches
                    </label>
                    <div className="grid gap-2">
                        <Label htmlFor="currency">Currency</Label>
                        <Input
                            id="currency"
                            maxLength={3}
                            value={form.currency}
                            onChange={(event) =>
                                setForm({ ...form, currency: event.target.value.toUpperCase() })
                            }
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="timezone">Timezone</Label>
                        <Input
                            id="timezone"
                            value={form.timezone}
                            onChange={(event) => setForm({ ...form, timezone: event.target.value })}
                        />
                    </div>
                    <div>
                        <Button type="submit" disabled={mutation.isPending}>
                            {mutation.isPending ? 'Saving…' : 'Save settings'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

function SubscriptionCard() {
    const { data, isPending } = useQuery(subscriptionQueryOptions());

    return (
        <Card>
            <CardHeader>
                <CardTitle>Subscription</CardTitle>
                <CardDescription>
                    Package limits are enforced by the API, not by this screen.
                </CardDescription>
            </CardHeader>
            <CardContent>
                {isPending ? (
                    <p className="text-muted-foreground text-sm">Loading subscription…</p>
                ) : null}
                {!isPending && data === null ? (
                    <p className="text-muted-foreground text-sm">
                        No subscription is attached to this pharmacy yet.
                    </p>
                ) : null}
                {data ? (
                    <dl className="grid gap-3 text-sm">
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">Status</dt>
                            <dd>
                                <StatusBadge status={data.status} />
                            </dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">Package</dt>
                            <dd>{data.package?.name ?? '—'}</dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">Write access</dt>
                            <dd>{data.is_read_only ? 'Read-only' : 'Allowed'}</dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">Period ends</dt>
                            <dd>
                                {data.current_period_ends_at
                                    ? formatLocalDateTime(data.current_period_ends_at)
                                    : '—'}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">Max branches</dt>
                            <dd>{data.package?.max_branches ?? '—'}</dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">Max users</dt>
                            <dd>{data.package?.max_users ?? '—'}</dd>
                        </div>
                    </dl>
                ) : null}
            </CardContent>
        </Card>
    );
}
