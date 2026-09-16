import { type ReactNode, useEffect, useRef } from 'react';
import { cn } from '@/shared/lib/utils';

type DialogProps = {
    open: boolean;
    title: string;
    description?: string;
    onOpenChange: (open: boolean) => void;
    children: ReactNode;
    className?: string;
};

export function Dialog({
    open,
    title,
    description,
    onOpenChange,
    children,
    className,
}: DialogProps) {
    const ref = useRef<HTMLDialogElement>(null);

  useEffect(() => {
    const node = ref.current;

    if (!node) {
      return;
    }

    if (open && !node.open) {
      node.showModal();
    }

    if (!open && node.open) {
      node.close();
    }

    return () => {
      if (node.open) {
        node.close();
      }
    };
  }, [open]);

    return (
        <dialog
            ref={ref}
            className={cn(
                'bg-background text-foreground w-full max-w-lg rounded-xl border p-0 shadow-lg',
                'backdrop:bg-black/40',
                className,
            )}
            onClose={() => onOpenChange(false)}
        >
            <div className="grid gap-4 p-6">
                <div className="grid gap-1">
                    <h2 className="text-lg font-semibold">{title}</h2>
                    {description ? (
                        <p className="text-muted-foreground text-sm">{description}</p>
                    ) : null}
                </div>
                {children}
            </div>
        </dialog>
    );
}
