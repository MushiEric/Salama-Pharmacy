import { flexRender, getCoreRowModel, useReactTable, type ColumnDef } from '@tanstack/react-table';
import { Button } from '@/shared/components/ui/button';
import { cn } from '@/shared/lib/utils';

type DataTableProps<T> = {
    columns: ColumnDef<T, unknown>[];
    data: T[];
    empty?: string;
    page?: number;
    lastPage?: number;
    onPageChange?: (page: number) => void;
};

export function DataTable<T>({
    columns,
    data,
    empty = 'No records yet.',
    page,
    lastPage,
    onPageChange,
}: DataTableProps<T>) {
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <div className="grid gap-3">
            <div className="bg-background overflow-x-auto rounded-lg border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/40 border-b text-left">
                        {table.getHeaderGroups().map((headerGroup) => (
                            <tr key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <th key={header.id} className="px-3 py-2 font-medium">
                                        {header.isPlaceholder
                                            ? null
                                            : flexRender(
                                                  header.column.columnDef.header,
                                                  header.getContext(),
                                              )}
                                    </th>
                                ))}
                            </tr>
                        ))}
                    </thead>
                    <tbody>
                        {table.getRowModel().rows.length === 0 ? (
                            <tr>
                                <td
                                    className="text-muted-foreground px-3 py-8 text-center"
                                    colSpan={columns.length}
                                >
                                    {empty}
                                </td>
                            </tr>
                        ) : (
                            table.getRowModel().rows.map((row) => (
                                <tr key={row.id} className="border-b last:border-0">
                                    {row.getVisibleCells().map((cell) => (
                                        <td key={cell.id} className="px-3 py-2 align-middle">
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext(),
                                            )}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
            {page !== undefined && lastPage !== undefined && onPageChange && lastPage > 1 ? (
                <div className="flex items-center justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={page <= 1}
                        onClick={() => onPageChange(page - 1)}
                    >
                        Previous
                    </Button>
                    <span className={cn('text-muted-foreground text-sm')}>
                        Page {page} of {lastPage}
                    </span>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={page >= lastPage}
                        onClick={() => onPageChange(page + 1)}
                    >
                        Next
                    </Button>
                </div>
            ) : null}
        </div>
    );
}
