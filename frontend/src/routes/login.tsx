import { createFileRoute, redirect } from '@tanstack/react-router';
import { z } from 'zod';
import { LoginForm } from '@/features/auth/login-form';
import { userQueryOptions } from '@/features/auth/api';

const loginSearchSchema = z.object({
    redirect: z.string().optional(),
});

export const Route = createFileRoute('/login')({
    validateSearch: loginSearchSchema,
    beforeLoad: async ({ context }) => {
        const user = await context.queryClient.ensureQueryData(userQueryOptions());

        if (user) {
            throw redirect({ to: '/app' });
        }
    },
    component: LoginPage,
});

function LoginPage() {
    return (
        <div className="bg-muted/40 flex min-h-screen items-center justify-center p-6">
            <LoginForm />
        </div>
    );
}
