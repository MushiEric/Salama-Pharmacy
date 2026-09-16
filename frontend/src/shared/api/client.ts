import { appConfig } from '@/app/config/env';

export class ApiError extends Error {
    readonly status: number;
    readonly body: unknown;

    constructor(message: string, status: number, body: unknown) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.body = body;
    }
}

type LaravelEnvelope<T> = {
    data?: T;
    message?: string;
    errors?: Record<string, string[]>;
};

export type Paginated<T> = {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
};

function readCookie(name: string): string | null {
    const prefix = `${name}=`;
    const found = document.cookie.split('; ').find((row) => row.startsWith(prefix));

    if (!found) {
        return null;
    }

    return decodeURIComponent(found.slice(prefix.length));
}

function xsrfToken(): string | null {
    return readCookie('XSRF-TOKEN');
}

async function parseBody(response: Response): Promise<unknown> {
    const text = await response.text();

    if (text === '') {
        return null;
    }

    try {
        return JSON.parse(text) as unknown;
    } catch {
        return text;
    }
}

export async function ensureCsrfCookie(): Promise<void> {
    await fetch(`${appConfig.apiUrl}/sanctum/csrf-cookie`, {
        credentials: 'include',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

export async function apiRaw(path: string, init: RequestInit = {}): Promise<unknown> {
    const headers = new Headers(init.headers);
    headers.set('Accept', 'application/json');
    headers.set('X-Requested-With', 'XMLHttpRequest');

    const token = xsrfToken();

    if (token) {
        headers.set('X-XSRF-TOKEN', token);
    }

    if (
        init.body !== undefined &&
        !(init.body instanceof FormData) &&
        !headers.has('Content-Type')
    ) {
        headers.set('Content-Type', 'application/json');
    }

    const response = await fetch(`${appConfig.apiUrl}${path}`, {
        ...init,
        credentials: 'include',
        headers,
    });

    const body = await parseBody(response);

    if (!response.ok) {
        const envelope = body as LaravelEnvelope<unknown> | null;
        const message = envelope?.message ?? `Request failed with status ${response.status}`;
        throw new ApiError(message, response.status, body);
    }

    return body;
}

export async function api<T>(path: string, init: RequestInit = {}): Promise<T> {
    const body = await apiRaw(path, init);

    if (body !== null && typeof body === 'object' && 'data' in body) {
        return (body as LaravelEnvelope<T>).data as T;
    }

    return body as T;
}

export function apiGet<T>(path: string): Promise<T> {
    return api<T>(path);
}

export async function apiGetPaginated<T>(path: string): Promise<Paginated<T>> {
    const body = await apiRaw(path);

    if (
        body === null ||
        typeof body !== 'object' ||
        !('data' in body) ||
        !('meta' in body) ||
        !Array.isArray((body as Paginated<T>).data)
    ) {
        throw new Error('Expected a paginated API response.');
    }

    return body as Paginated<T>;
}

export function apiSend<T>(path: string, method: string, body?: unknown): Promise<T> {
    return api<T>(path, {
        method,
        body: body === undefined ? undefined : JSON.stringify(body),
    });
}

export function validationErrors(error: unknown): Record<string, string> {
    if (!(error instanceof ApiError) || error.body === null || typeof error.body !== 'object') {
        return {};
    }

    const errors = (error.body as LaravelEnvelope<unknown>).errors;

    if (!errors) {
        return {};
    }

    return Object.fromEntries(
        Object.entries(errors).map(([key, messages]) => [key, messages[0] ?? 'Invalid value']),
    );
}
