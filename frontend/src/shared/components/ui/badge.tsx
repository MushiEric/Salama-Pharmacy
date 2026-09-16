import type { ComponentProps } from 'react';
import { cn } from '@/shared/lib/utils';

function Badge({ className, ...props }: ComponentProps<'span'>) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium capitalize',
                className,
            )}
            {...props}
        />
    );
}

export { Badge };
