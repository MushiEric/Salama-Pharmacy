import { queryOptions } from '@tanstack/react-query';
import { apiGet, apiSend, ApiError, ensureCsrfCookie } from '@/shared/api/client';
import type { AuthenticatedUser } from '@/shared/types/auth';

export const userQueryKey = ['auth', 'user'] as const;

export function userQueryOptions() {
    return queryOptions({
        queryKey: userQueryKey,
        queryFn: async (): Promise<AuthenticatedUser | null> => {
            try {
                return await apiGet<AuthenticatedUser>('/api/user');
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    return null;
                }

                throw error;
            }
        },
        retry: false,
        staleTime: 30_000,
    });
}

export async function login(email: string, password: string): Promise<AuthenticatedUser> {
    await ensureCsrfCookie();

    return apiSend<AuthenticatedUser>('/api/login', 'POST', { email, password });
}

export async function logout(): Promise<void> {
    await apiSend('/api/logout', 'POST');
}
