import type { ComponentProps } from 'react';
import { cn } from '@/shared/lib/utils';

function NativeSelect({ className, ...props }: ComponentProps<'select'>) {
    return (
        <select
            className={cn(
                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        />
    );
}

export { NativeSelect };
