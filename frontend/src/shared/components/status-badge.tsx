import type { ReactNode } from 'react';

type StatusBadgeProps = {
    status: string;
};

const styles: Record<string, string> = {
    active: 'border-transparent bg-emerald-100 text-emerald-800',
    inactive: 'border-transparent bg-muted text-muted-foreground',
    suspended: 'border-transparent bg-amber-100 text-amber-900',
    trial: 'border-transparent bg-sky-100 text-sky-900',
    expired: 'border-transparent bg-red-100 text-red-800',
    cancelled: 'border-transparent bg-muted text-muted-foreground',
};

export function StatusBadge({ status }: StatusBadgeProps): ReactNode {
    const className = styles[status] ?? 'bg-muted text-muted-foreground';

    return (
        <span
            className={`inline-flex rounded-md px-2 py-0.5 text-xs font-medium capitalize ${className}`}
        >
            {status}
        </span>
    );
}
