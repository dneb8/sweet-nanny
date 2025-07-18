<script lang="ts" setup>
import DataTable from '@/components/datatable/DataTable.vue'  // Ajusta la ruta según corresponda
import type { User } from '@/types/User'
import { generateColumns } from '@/components/datatable/Columns'

const props = defineProps<{
  resource: User[]
}>()

// Genera columnas automáticas para User (puedes definir columnas manuales si quieres)
const columns = generateColumns(props.resource)
</script>

<template>
  <DataTable :data="resource" :columns="columns" />
</template>
