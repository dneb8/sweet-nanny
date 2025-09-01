import type { ColumnDef } from '@tanstack/vue-table';

export function generateColumns<TData extends Record<string, any>>(
  data: TData[]
): ColumnDef<TData, any>[] {
  if (!data.length) return [];

  return Object.keys(data[0]).map((key) => ({
    accessorKey: key,
    header: key.charAt(0).toUpperCase() + key.slice(1),
    cell: ({ row }: any) => row.getValue(key),
  })) as ColumnDef<TData, any>[]; // <-- casteo aquí
}
