<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { Input } from '@/components/ui/input'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious
} from '@/components/ui/pagination'
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover'

const props = defineProps<{
  items: any[]
  searchables: string[]
  sortables: string[]
  FilterPanel: object
  perPage?: number
}>()

const currentPage = ref(1)
const filterValue = ref('')
const selectedFilters = ref<{ [key: string]: any }>({})
const filterFn = ref<null | ((item: any) => boolean)>(null)

const perPage = computed(() => props.perPage ?? 12)

// 🔍 búsqueda global
const searchedItems = computed(() => {
  return props.items.filter(item =>
    props.searchables.some(column =>
      String(item[column] ?? '')
        .toLowerCase()
        .includes(filterValue.value.toLowerCase())
    )
  )
})

// 🔎 aplica filtro extra del panel
const filteredItems = computed(() => {
  return filterFn.value
    ? searchedItems.value.filter(item => filterFn.value!(item))
    : searchedItems.value
})

// 📄 paginación
const lastPage = computed(() =>
  Math.max(1, Math.ceil(filteredItems.value.length / perPage.value))
)

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredItems.value.slice(start, start + perPage.value)
})

// resetear página si cambia algo
watch([filterValue, selectedFilters], () => {
  currentPage.value = 1
}, { deep: true })

// 📑 páginas visibles
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

const popoverOpen = ref(false)
function handleResize() {
  if (window.innerWidth >= 768 && popoverOpen.value) {
    popoverOpen.value = false
  }
}
onMounted(() => window.addEventListener('resize', handleResize))
onUnmounted(() => window.removeEventListener('resize', handleResize))
</script>

<template>
  <div class="space-y-4">
    <div class="relative w-full flex items-center gap-2">
      <div class="relative flex-1">
        <Input v-model="filterValue" placeholder="Buscar..." class="pl-10" />
        <span class="absolute left-2 inset-y-0 flex items-center text-gray-400">
          <Icon icon="mdi:magnify" class="h-5 w-5" />
        </span>
      </div>

      <!-- Popover con filtros -->
      <Popover v-model:open="popoverOpen">
        <PopoverTrigger as-child>
          <button class="p-2 rounded-md hover:bg-muted transition">
            <Icon icon="mdi:filter-variant" class="h-5 w-5 text-gray-500" />
          </button>
        </PopoverTrigger>
        <PopoverContent class="w-[300px] p-4">
          <component
            :is="FilterPanel"
            :show="true"
            :sortables="props.sortables"
            v-model:selectedFilters="selectedFilters"
            v-model:filterFn="filterFn"
            @closePopover="popoverOpen = false"
          />
        </PopoverContent>
      </Popover>  
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 w-full">
      <slot v-for="item in paginatedItems" :item="item" :key="item.id" />
    </div>

    <!-- Paginación -->
    <div class="mt-auto">
      <Pagination
        v-if="lastPage > 1"
        class="justify-center"
        :items-per-page="perPage"
        :total-items="filteredItems.length"
      >
        <PaginationContent class="flex items-center space-x-5">
          <PaginationItem :value="currentPage">
            <PaginationPrevious
              :disabled="currentPage <= 1"
              @click="currentPage > 1 && (currentPage--)"
            />
          </PaginationItem>

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

          <PaginationItem :value="currentPage">
            <PaginationNext
              :disabled="currentPage >= lastPage"
              @click="currentPage < lastPage && (currentPage++)"
            />
          </PaginationItem>
        </PaginationContent>
      </Pagination>
    </div>
  </div>
</template>
