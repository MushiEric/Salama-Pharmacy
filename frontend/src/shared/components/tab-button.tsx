import { cn } from '@/shared/lib/utils';

type TabButtonProps = {
    active: boolean;
    onClick: () => void;
    children: string;
};

export function TabButton({ active, onClick, children }: TabButtonProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'rounded-md px-3 py-1.5 text-sm',
                active ? 'bg-background shadow-sm' : 'text-muted-foreground hover:text-foreground',
            )}
        >
            {children}
        </button>
    );
}
