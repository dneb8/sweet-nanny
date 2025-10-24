/**
 * Columns Helper
 * ==============
 * 
 * Helper para generar definiciones de columnas automáticamente para TanStack Table.
 * Basado en las claves del primer objeto del array de datos.
 * 
 * @function generateColumns<TData>
 * @param data - Array de objetos (datos de la tabla)
 * @returns Array de ColumnDef compatible con @tanstack/vue-table
 * 
 * Comportamiento:
 *   - Si data está vacío, retorna []
 *   - Para cada clave en data[0]:
 *     - accessorKey: la clave misma (ej: "name")
 *     - header: la clave capitalizada (ej: "Name")
 *     - cell: renderiza el valor usando row.getValue(key)
 * 
 * Ejemplo:
 *   const users = [{ name: 'Juan', email: 'juan@example.com' }]
 *   const columns = generateColumns(users)
 *   // Genera: [
 *   //   { accessorKey: 'name', header: 'Name', cell: ... },
 *   //   { accessorKey: 'email', header: 'Email', cell: ... }
 *   // ]
 * 
 * 📖 Documentación completa: ./README.md
 */

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
