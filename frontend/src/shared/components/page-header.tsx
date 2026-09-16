import type { ReactNode } from 'react';

type PageHeaderProps = {
    title: string;
    description?: string;
    actions?: ReactNode;
};

export function PageHeader({ title, description, actions }: PageHeaderProps) {
    return (
        <div className="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div className="grid gap-1">
                <h1 className="text-2xl font-semibold">{title}</h1>
                {description ? (
                    <p className="text-muted-foreground text-sm">{description}</p>
                ) : null}
            </div>
            {actions}
        </div>
    );
}
