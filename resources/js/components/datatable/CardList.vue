<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Input } from '@/components/ui/input'
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationNext,
  PaginationPrevious
} from '@/components/ui/pagination'

const props = defineProps<{
  items: any[]
  filterColumn: string
}>()

const currentPage = ref(1)
const perPage = 9
const filterValue = ref('')

// Filtrar los datos
const filteredItems = computed(() => {
  return props.items.filter((item) =>
    String(item[props.filterColumn]).toLowerCase().includes(filterValue.value.toLowerCase())
  )
})

// Calcular cuántas páginas hay
const lastPage = computed(() => Math.ceil(filteredItems.value.length / perPage))

// Obtener los ítems a mostrar en esta página
const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredItems.value.slice(start, start + perPage)
})

// Resetear página cuando se filtra
watch(filterValue, () => {
  currentPage.value = 1
})
</script>

<template>
  <div class="space-y-4">
    <!-- Input de búsqueda -->
    <Input
      class="max-w-sm"
      :placeholder="`Filtrar por ${filterColumn}...`"
      v-model="filterValue"
    />

    <!-- Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <slot v-for="item in paginatedItems" :item="item" :key="item.id" />
    </div>

    <!-- Paginación -->
    <Pagination v-if="lastPage > 1" class="justify-center">
      <PaginationContent>
        <PaginationItem>
          <PaginationPrevious
            :disabled="currentPage <= 1"
            @click="currentPage--"
          />
        </PaginationItem>
        <PaginationItem>
          <PaginationNext
            :disabled="currentPage >= lastPage"
            @click="currentPage++"
          />
        </PaginationItem>
      </PaginationContent>
    </Pagination>
  </div>
</template>
