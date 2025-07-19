<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Input } from '@/components/ui/input'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious
} from '@/components/ui/pagination'

const props = defineProps<{
  items: any[]
  searchables: string[]
  sortables: string[]
}>()

const currentPage = ref(1)
const perPage = 12
const filterValue = ref('')

// Filtrar los datos en búsquedas
const filteredItems = computed(() =>
  props.items.filter(item =>
    props.searchables.some(column =>
      String(item[column] ?? '')
        .toLowerCase()
        .includes(filterValue.value.toLowerCase())
    )
  )
)

// Calcular páginas totales
const lastPage = computed(() =>
  Math.max(1, Math.ceil(filteredItems.value.length / perPage))
)

// Obtener los ítems paginados
const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredItems.value.slice(start, start + perPage)
})

// Resetear página si cambia el filtro
watch(filterValue, () => {
  currentPage.value = 1
})

// Páginas visibles para la paginación
const pagesToShow = computed(() => {
  const total = lastPage.value
  const current = currentPage.value
  const range: (number | string)[] = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) range.push(i)
  } else {
    range.push(1)
    if (current > 3) range.push('...')
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)
    for (let i = start; i <= end; i++) range.push(i)
    if (current < total - 2) range.push('...')
    range.push(total)
  }

  return range
})
</script>

<template>
  <div class="space-y-4"> 
    <div class="relative w-full items-center">
        <Input v-model="filterValue"  :placeholder="`Buscar usuarios`" class="pl-10" />
        <span class="absolute start-1 inset-y-0 flex items-center justify-center px-2">
        <Icon icon="mdi:magnify" class="h-5 w-5 text-gray-400" />
        </span>
    </div>
    <!-- Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <slot v-for="item in paginatedItems" :item="item" :key="item.id" />
    </div>

    <!-- Paginación -->
    <Pagination 
        v-if="lastPage > 1"
        class="justify-center" 
        :items-per-page="perPage" 
        :total-items="filteredItems.length"
    >
      <PaginationContent class="flex items-center space-x-5">
        <!-- Anterior -->
        <PaginationItem :value="currentPage">
          <PaginationPrevious
            :disabled="currentPage <= 1"
            @click="currentPage > 1 && (currentPage--)"
          />
        </PaginationItem>

        <!-- Números -->
        <template v-for="(p, index) in pagesToShow" :key="index">
          <PaginationItem
            v-if="typeof p === 'number'"
            :value="p"
            :is-active="p === currentPage"
            @click="currentPage = p"
          >
            {{ p }}
          </PaginationItem>
          <PaginationItem v-else :value="0" disabled>
            <PaginationEllipsis />
          </PaginationItem>
        </template>

        <!-- Siguiente -->
        <PaginationItem :value="currentPage">
          <PaginationNext
            :disabled="currentPage >= lastPage"
            @click="currentPage < lastPage && (currentPage++)"
          />
        </PaginationItem>
      </PaginationContent>
    </Pagination>
  </div>
</template>
