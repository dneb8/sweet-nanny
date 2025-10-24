<!--
  DataTable Component
  ===================
  
  Componente de tabla tradicional basado en @tanstack/vue-table (TanStack Table v8).
  Ideal para visualización tabular de datos con soporte para sorting, filtering, y más.
  
  Props:
    - columns: ColumnDef<TData, TValue>[] (opcional) - Definiciones de columnas TanStack
    - data: TData[] (requerido) - Array de datos a mostrar
  
  Características:
    - Auto-generación de columnas si no se proporcionan
    - Tabla responsive usando componentes shadcn-vue
    - Empty state cuando no hay datos
  
  Ejemplo de uso:
    <DataTable :data="users" :columns="columns" />
  
  📖 Documentación completa: ./README.md
-->

<script setup lang="ts" generic="TData extends Record<string, any>, TValue">
import type { ColumnDef } from '@tanstack/vue-table'
import { generateColumns } from './Columns';
import {
  FlexRender,
  getCoreRowModel,
  useVueTable,
} from '@tanstack/vue-table'

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const props = defineProps<{
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
}>()

const columnsToUse = props.columns && props.columns.length > 0
  ? props.columns
  : generateColumns(props.data)

const table = useVueTable({
  get data() { return props.data },
  get columns() { return columnsToUse },
  getCoreRowModel: getCoreRowModel(),
})
</script>

<template>
  <div class="border rounded-md">
    <Table>
      <TableHeader>
        <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
          <TableHead v-for="header in headerGroup.headers" :key="header.id">
            <FlexRender
              v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
              :props="header.getContext()"
            />
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <template v-if="table.getRowModel().rows?.length">
          <TableRow
            v-for="row in table.getRowModel().rows" :key="row.id"
            :data-state="row.getIsSelected() ? 'selected' : undefined"
          >
            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
              <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
            </TableCell>
          </TableRow>
        </template>
        <template v-else>
          <TableRow>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
    </Table>
  </div>
</template>