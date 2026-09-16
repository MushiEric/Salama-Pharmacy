import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useNavigate, useSearch } from '@tanstack/react-router';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';
import { z } from 'zod';
import { login, userQueryKey } from '@/features/auth/api';
import { ApiError } from '@/shared/api/client';
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

const loginSchema = z.object({
    email: z.string().email(),
    password: z.string().min(1),
});

type LoginValues = z.infer<typeof loginSchema>;

export function LoginForm() {
    const navigate = useNavigate();
    const search = useSearch({ from: '/login' });
    const queryClient = useQueryClient();
    const form = useForm<LoginValues>({
        resolver: zodResolver(loginSchema),
        defaultValues: { email: '', password: '' },
    });

    const mutation = useMutation({
        mutationFn: (values: LoginValues) => login(values.email, values.password),
        onSuccess: async (user) => {
            queryClient.setQueryData(userQueryKey, user);
            toast.success(`Welcome back, ${user.name}`);
            await navigate({ to: search.redirect ?? '/app' });
        },
        onError: (error) => {
            if (error instanceof ApiError && error.status === 422) {
                toast.error('Invalid email or password.');
                return;
            }

            toast.error(error instanceof Error ? error.message : 'Unable to sign in.');
        },
    });

    return (
        <Card className="w-full max-w-md">
            <CardHeader>
                <CardTitle>Sign in</CardTitle>
                <CardDescription>Cookie-based session via Laravel Sanctum.</CardDescription>
            </CardHeader>
            <CardContent>
                <form
                    className="grid gap-4"
                    onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
                >
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            autoComplete="email"
                            {...form.register('email')}
                        />
                        {form.formState.errors.email ? (
                            <p className="text-destructive text-sm">
                                {form.formState.errors.email.message}
                            </p>
                        ) : null}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            {...form.register('password')}
                        />
                        {form.formState.errors.password ? (
                            <p className="text-destructive text-sm">
                                {form.formState.errors.password.message}
                            </p>
                        ) : null}
                    </div>
                    <Button type="submit" disabled={mutation.isPending}>
                        {mutation.isPending ? 'Signing in…' : 'Sign in'}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
