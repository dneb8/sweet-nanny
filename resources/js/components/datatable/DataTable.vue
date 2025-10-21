<script setup lang="ts" generic="TData extends Record<string, any>">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

export interface DataTableColumn<T = any> {
    id: string;
    header: string;
    accessorKey?: keyof T;
    cell?: (row: T) => any;
    sortable?: boolean;
    filterable?: boolean;
    filterType?: 'text' | 'select' | 'date' | 'number';
    filterOptions?: Array<{ label: string; value: string | number | boolean }>;
    headerClass?: string;
    cellClass?: string;
}

export interface PaginationLinks {
    prev?: string | null;
    next?: string | null;
}

interface Props {
    columns: DataTableColumn<TData>[];
    data: TData[];
    // Pagination props
    links?: PaginationLinks;
    page?: number;
    perPage?: number;
    total?: number;
    lastPage?: number;
    // Optional card slot for responsive view
    cardSlot?: boolean;
    // Initial sort state
    sortBy?: string | null;
    sortDir?: 'asc' | 'desc' | null;
    // Initial search value
    searchQuery?: string;
    // Column filters
    columnFilters?: Record<string, string | number | boolean | null>;
    // View mode
    viewMode?: 'auto' | 'table' | 'cards';
}

const props = withDefaults(defineProps<Props>(), {
    links: () => ({ prev: null, next: null }),
    page: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
    cardSlot: false,
    sortBy: null,
    sortDir: null,
    searchQuery: '',
    columnFilters: () => ({}),
    viewMode: 'auto',
});

const emit = defineEmits<{
    search: [value: string];
    'sort:change': [{ id: string; direction: 'asc' | 'desc' | null }];
    goto: [url: string];
    'change:perPage': [perPage: number];
    'filters:change': [filters: Record<string, string | number | boolean | null>];
    'view:change': [view: 'table' | 'cards' | 'auto'];
}>();

// Search state
const searchValue = ref(props.searchQuery || '');

// Column visibility state
const columnVisibility = ref<Record<string, boolean>>({});

// Column filters state
const activeFilters = ref<Record<string, string | number | boolean | null>>({ ...props.columnFilters });
const filtersPanelOpen = ref(false);

// View mode state
const currentViewMode = ref<'table' | 'cards' | 'auto'>(props.viewMode);

// Initialize column visibility
onMounted(() => {
    props.columns.forEach((col) => {
        columnVisibility.value[col.id] = true;
    });
});

// Sort state
const sortState = ref<{ id: string | null; direction: 'asc' | 'desc' | null }>({
    id: props.sortBy || null,
    direction: props.sortDir || null,
});

// Visible columns
const visibleColumns = computed(() => {
    return props.columns.filter((col) => columnVisibility.value[col.id] !== false);
});

// Handle search
function handleSearch() {
    emit('search', searchValue.value);
}

// Handle filters
function handleFilterChange(columnId: string, value: string | number | boolean | null) {
    activeFilters.value[columnId] = value;
}

function applyFilters() {
    emit('filters:change', { ...activeFilters.value });
    filtersPanelOpen.value = false;
}

function clearFilters() {
    activeFilters.value = {};
    emit('filters:change', {});
}

// Handle view mode change
function handleViewModeChange(mode: 'table' | 'cards' | 'auto') {
    currentViewMode.value = mode;
    emit('view:change', mode);
}

// Handle sort
function handleSort(columnId: string) {
    const column = props.columns.find((col) => col.id === columnId);
    if (!column?.sortable) return;

    if (sortState.value.id === columnId) {
        // Cycle through: asc -> desc -> null
        if (sortState.value.direction === 'asc') {
            sortState.value.direction = 'desc';
        } else if (sortState.value.direction === 'desc') {
            sortState.value.direction = null;
            sortState.value.id = null;
        }
    } else {
        sortState.value.id = columnId;
        sortState.value.direction = 'asc';
    }

    emit('sort:change', {
        id: sortState.value.id || '',
        direction: sortState.value.direction,
    });
}

// Get cell value
function getCellValue(row: TData, column: DataTableColumn<TData>) {
    if (column.cell) {
        return column.cell(row);
    }
    if (column.accessorKey) {
        return row[column.accessorKey];
    }
    return '';
}

// Responsive view detection
const isMobile = ref(false);

function checkMobile() {
    isMobile.value = window.innerWidth < 768; // md breakpoint
}

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

// Computed view - respects viewMode prop
const shouldShowCards = computed(() => {
    if (currentViewMode.value === 'table') return false;
    if (currentViewMode.value === 'cards') return true;
    // 'auto' mode - use responsive detection
    return isMobile.value;
});

// Pagination
const pagesToShow = computed(() => {
    const total = props.lastPage || 1;
    const current = props.page || 1;
    const range: (number | string)[] = [];

    if (total <= 7) {
        for (let i = 1; i <= total; i++) range.push(i);
    } else {
        range.push(1);
        if (current > 3) range.push('...');
        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);
        for (let i = start; i <= end; i++) range.push(i);
        if (current < total - 2) range.push('...');
        range.push(total);
    }
    return range;
});

function handlePageChange(page: number) {
    // Emit goto event - parent should handle URL construction
    emit('goto', `?page=${page}`);
}
</script>

<template>
    <div class="space-y-4">
        <!-- Search and controls bar -->
        <div class="flex items-center gap-2">
            <!-- Search input with button -->
            <div class="relative flex-1 flex gap-2">
                <div class="relative flex-1">
                    <Input v-model="searchValue" placeholder="Buscar..." @keydown.enter="handleSearch" />
                </div>
                <Button @click="handleSearch" size="icon" variant="outline">
                    <Icon icon="basil:search-outline" class="h-5 w-5" />
                </Button>
            </div>

            <!-- Filters button -->
            <Popover v-if="columns.some(col => col.filterable)" v-model:open="filtersPanelOpen">
                <PopoverTrigger as-child>
                    <Button variant="outline" size="icon" :class="{ 'bg-primary/10': Object.keys(activeFilters).some(key => activeFilters[key]) }">
                        <Icon icon="mdi:filter-variant" class="h-5 w-5" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent align="end" class="w-[320px]">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-sm">Filtros</h4>
                            <Button variant="ghost" size="sm" @click="clearFilters">
                                <Icon icon="mdi:close" class="h-4 w-4 mr-1" />
                                Limpiar
                            </Button>
                        </div>
                        
                        <div v-for="column in columns.filter(col => col.filterable)" :key="column.id" class="space-y-2">
                            <label class="text-sm font-medium">{{ column.header }}</label>
                            
                            <!-- Text filter -->
                            <Input
                                v-if="!column.filterType || column.filterType === 'text'"
                                :model-value="activeFilters[column.id] as string"
                                @update:model-value="(val) => handleFilterChange(column.id, val)"
                                @keydown.enter="applyFilters"
                                placeholder="Filtrar..."
                            />
                            
                            <!-- Number filter -->
                            <Input
                                v-else-if="column.filterType === 'number'"
                                type="number"
                                :model-value="activeFilters[column.id] as number"
                                @update:model-value="(val) => handleFilterChange(column.id, val)"
                                @keydown.enter="applyFilters"
                                placeholder="Filtrar..."
                            />
                            
                            <!-- Select filter -->
                            <Select
                                v-else-if="column.filterType === 'select' && column.filterOptions"
                                :model-value="activeFilters[column.id] as string"
                                @update:model-value="(val) => handleFilterChange(column.id, val)"
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem
                                        v-for="option in column.filterOptions"
                                        :key="option.value"
                                        :value="String(option.value)"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                        <div class="flex justify-end">
                            <Button @click="applyFilters" size="sm">
                                <Icon icon="mdi:check" class="h-4 w-4 mr-1" />
                                Aplicar
                            </Button>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>

            <!-- Columns menu -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="icon">
                        <Icon icon="mdi:view-column" class="h-5 w-5" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuLabel>Columnas</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuCheckboxItem
                        v-for="column in columns"
                        :key="column.id"
                        :checked="columnVisibility[column.id] !== false"
                        @update:checked="(value) => (columnVisibility[column.id] = value)"
                    >
                        {{ column.header }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- View toggle -->
            <slot name="view-toggle">
                <ToggleGroup type="single" :model-value="currentViewMode" @update:model-value="handleViewModeChange">
                    <ToggleGroupItem value="table" aria-label="Vista tabla">
                        <Icon icon="mdi:table" class="h-5 w-5" />
                    </ToggleGroupItem>
                    <ToggleGroupItem value="cards" aria-label="Vista tarjetas">
                        <Icon icon="mdi:view-grid" class="h-5 w-5" />
                    </ToggleGroupItem>
                    <ToggleGroupItem value="auto" aria-label="Vista automática">
                        <Icon icon="mdi:auto-fix" class="h-5 w-5" />
                    </ToggleGroupItem>
                </ToggleGroup>
            </slot>

            <!-- Additional slot for custom controls -->
            <slot name="controls" />
        </div>

        <!-- Table view (desktop) or Cards view (mobile) -->
        <div v-if="!shouldShowCards || !cardSlot" class="border rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-for="column in visibleColumns" :key="column.id" :class="column.headerClass">
                                <button
                                    v-if="column.sortable"
                                    @click="handleSort(column.id)"
                                    class="flex items-center gap-1 hover:text-foreground transition-colors"
                                >
                                    {{ column.header }}
                                    <Icon
                                        v-if="sortState.id === column.id && sortState.direction === 'asc'"
                                        icon="mdi:chevron-up"
                                        class="h-4 w-4"
                                    />
                                    <Icon
                                        v-else-if="sortState.id === column.id && sortState.direction === 'desc'"
                                        icon="mdi:chevron-down"
                                        class="h-4 w-4"
                                    />
                                    <Icon v-else icon="basil:sort-outline" class="h-4 w-4 text-muted-foreground" />
                                </button>
                                <span v-else>{{ column.header }}</span>
                            </TableHead>
                            <!-- Actions slot header -->
                            <TableHead v-if="$slots.actions" class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="data.length > 0">
                            <TableRow v-for="(row, index) in data" :key="index">
                                <TableCell v-for="column in visibleColumns" :key="column.id" :class="column.cellClass">
                                    <slot :name="`cell-${column.id}`" :row="row" :value="getCellValue(row, column)">
                                        {{ getCellValue(row, column) }}
                                    </slot>
                                </TableCell>
                                <!-- Actions slot -->
                                <TableCell v-if="$slots.actions" class="text-right">
                                    <slot name="actions" :row="row" />
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell :colspan="visibleColumns.length + ($slots.actions ? 1 : 0)" class="h-24 text-center">
                                    <slot name="empty">
                                        <div class="text-muted-foreground">No se encontraron resultados.</div>
                                    </slot>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Cards view (mobile with slot) -->
        <div v-else-if="shouldShowCards && cardSlot" class="grid grid-cols-1 gap-4">
            <slot name="card" v-for="(row, index) in data" :key="index" :row="row" />
            <div v-if="data.length === 0" class="text-center py-8 text-muted-foreground">
                <slot name="empty">No se encontraron resultados.</slot>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage && lastPage > 1" class="mt-4">
            <Pagination class="justify-center" :items-per-page="perPage" :total-items="total">
                <PaginationContent class="flex items-center space-x-5">
                    <PaginationItem :value="page">
                        <PaginationPrevious :disabled="!links?.prev" @click="links?.prev && emit('goto', links.prev)" />
                    </PaginationItem>

                    <template v-for="(p, index) in pagesToShow" :key="index">
                        <PaginationItem v-if="typeof p === 'number'" :value="p" :is-active="p === page" @click="handlePageChange(p)">
                            {{ p }}
                        </PaginationItem>
                        <PaginationItem v-else :value="0" disabled>
                            <PaginationEllipsis />
                        </PaginationItem>
                    </template>

                    <PaginationItem :value="page">
                        <PaginationNext :disabled="!links?.next" @click="links?.next && emit('goto', links.next)" />
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>

        <!-- Results info -->
        <div v-if="total" class="text-sm text-muted-foreground text-center">
            Mostrando {{ (page - 1) * perPage + 1 }} a {{ Math.min(page * perPage, total) }} de {{ total }} resultados
        </div>
    </div>
</template>