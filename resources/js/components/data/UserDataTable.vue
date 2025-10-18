<script setup lang="ts">
import { computed, ref, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import type { User } from '@/types/User';
import type { FetcherResponse } from '@/types/FetcherResponse';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { Badge } from '@/components/ui/badge';
import UserCard from '@/Pages/User/partials/UserCard.vue';
import { useDataTable } from '@/composables/useDataTable';
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum';
import { ChevronUp, ChevronDown } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';

const props = defineProps<{
    users: FetcherResponse<User>;
}>();

const { state, loading, setSearch, setFilter, clearFilters, setSort, setPage, setView, searchInputRef } = useDataTable('users.index', {
    view: 'table',
    per_page: 15,
});

// Popover state
const popoverOpen = ref(false);

// Compute pagination pages to show
const pagesToShow = computed(() => {
    const total = props.users.last_page;
    const current = props.users.current_page;
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

// Check if a column is sorted
function isSorted(field: string): 'asc' | 'desc' | false {
    if (state.value.sort === field) {
        return state.value.dir;
    }
    return false;
}

// Handle column sort
function handleSort(field: string) {
    setSort(field);
}

// Handle filter changes
function handleRoleFilter(role: string) {
    setFilter('role', state.value.filters.role === role ? null : role);
}

function handleVerifiedFilter(value: string) {
    setFilter('verified', value);
}

function handleResetFilters() {
    clearFilters();
    popoverOpen.value = false;
}

// Format date
function formatDate(date: string | null): string {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// Handle delete
function handleDelete(user: User) {
    if (confirm(`¿Estás seguro de que deseas eliminar a ${user.name} ${user.surnames}? Esta acción no se puede deshacer.`)) {
        router.delete(route('users.destroy', user.ulid));
    }
}

// Get badge variant for role
function getRoleBadgeVariant(role: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (role) {
        case RoleEnum.ADMIN:
            return 'destructive';
        case RoleEnum.TUTOR:
            return 'default';
        case RoleEnum.NANNY:
            return 'secondary';
        default:
            return 'outline';
    }
}
</script>

<template>
    <div class="space-y-4">
        <!-- Search and filters bar -->
        <div class="flex items-center gap-2">
            <!-- Search input -->
            <div class="relative flex-1">
                <Input
                    ref="searchInputRef"
                    :model-value="state.search"
                    @update:model-value="setSearch"
                    placeholder="Buscar por nombre, apellidos o email..."
                    class="pl-10"
                />
                <span class="absolute left-2 inset-y-0 flex items-center text-gray-400">
                    <Icon icon="mdi:magnify" class="h-5 w-5" />
                </span>
            </div>

            <!-- Filter button -->
            <Popover v-model:open="popoverOpen">
                <PopoverTrigger as-child>
                    <Button variant="outline" size="icon">
                        <Icon icon="mdi:filter-variant" class="h-5 w-5" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-[300px]">
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold">Filtrar por:</h4>

                        <!-- Role filter -->
                        <div>
                            <Label>Rol</Label>
                            <div class="flex flex-col gap-2 mt-2">
                                <div v-for="roleKey in Object.values(RoleEnum)" :key="roleKey" class="flex items-center space-x-2">
                                    <Checkbox
                                        :checked="state.filters.role === roleKey"
                                        @click="handleRoleFilter(roleKey)"
                                        :id="`role-${roleKey}`"
                                    />
                                    <label :for="`role-${roleKey}`" class="text-sm cursor-pointer">
                                        {{ getRoleLabelByString(roleKey) }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Email verified filter -->
                        <div>
                            <Label>Verificación de correo</Label>
                            <ToggleGroup type="single" :model-value="state.filters.verified || null" @update:model-value="handleVerifiedFilter" class="flex gap-2 mt-2">
                                <ToggleGroupItem value="verified" aria-label="Verificados"> Verificados </ToggleGroupItem>
                                <ToggleGroupItem value="unverified" aria-label="No verificados"> No verificados </ToggleGroupItem>
                            </ToggleGroup>
                        </div>

                        <!-- Reset button -->
                        <div class="flex justify-end">
                            <Button @click="handleResetFilters" variant="outline" size="sm">
                                <Icon icon="solar:restart-circle-linear" class="size-4 mr-2" />
                                Limpiar filtros
                            </Button>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>

            <!-- View toggle -->
            <ToggleGroup type="single" :model-value="state.view" @update:model-value="setView">
                <ToggleGroupItem value="table" aria-label="Vista tabla">
                    <Icon icon="mdi:table" class="h-5 w-5" />
                </ToggleGroupItem>
                <ToggleGroupItem value="cards" aria-label="Vista tarjetas">
                    <Icon icon="mdi:view-grid" class="h-5 w-5" />
                </ToggleGroupItem>
            </ToggleGroup>

            <!-- Loading indicator -->
            <div v-if="loading" class="text-sm text-muted-foreground">
                <Icon icon="mdi:loading" class="h-5 w-5 animate-spin" />
            </div>
        </div>

        <!-- Table view -->
        <div v-if="state.view === 'table'" class="border rounded-lg overflow-hidden relative">
            <!-- Loading overlay -->
            <div
                v-if="loading"
                class="absolute inset-0 bg-background/60 backdrop-blur-sm z-10 flex items-center justify-center"
                aria-busy="true"
                role="status"
            >
                <div class="flex flex-col items-center gap-2">
                    <Icon icon="mdi:loading" class="w-8 h-8 animate-spin text-primary" />
                    <span class="text-sm text-muted-foreground">Cargando...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <Table>
                    <TableHeader class="sticky top-0 bg-muted/50 backdrop-blur supports-[backdrop-filter]:bg-muted/50">
                        <TableRow>
                            <TableHead>
                                <button @click="handleSort('name')" class="flex items-center gap-1 hover:text-foreground transition-colors">
                                    Nombre
                                    <ChevronUp v-if="isSorted('name') === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-else-if="isSorted('name') === 'desc'" class="h-4 w-4" />
                                </button>
                            </TableHead>
                            <TableHead>
                                <button @click="handleSort('email')" class="flex items-center gap-1 hover:text-foreground transition-colors">
                                    Email
                                    <ChevronUp v-if="isSorted('email') === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-else-if="isSorted('email') === 'desc'" class="h-4 w-4" />
                                </button>
                            </TableHead>
                            <TableHead>
                                <button @click="handleSort('role')" class="flex items-center gap-1 hover:text-foreground transition-colors">
                                    Rol
                                    <ChevronUp v-if="isSorted('role') === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-else-if="isSorted('role') === 'desc'" class="h-4 w-4" />
                                </button>
                            </TableHead>
                            <TableHead>
                                <button @click="handleSort('created_at')" class="flex items-center gap-1 hover:text-foreground transition-colors">
                                    Fecha de creación
                                    <ChevronUp v-if="isSorted('created_at') === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-else-if="isSorted('created_at') === 'desc'" class="h-4 w-4" />
                                </button>
                            </TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="users.data.length > 0">
                            <TableRow v-for="(user, index) in users.data" :key="user.ulid" :class="{ 'bg-muted/30': index % 2 === 1 }">
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <span>{{ user.name }} {{ user.surnames }}</span>
                                        <Icon v-if="user.email_verified_at" icon="mdi:check-decagram" class="w-4 h-4 text-emerald-500" />
                                    </div>
                                </TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getRoleBadgeVariant(user.roles?.[0]?.name ?? '')">
                                        {{ getRoleLabelByString(user.roles?.[0]?.name ?? '') ?? 'Sin rol' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(user.created_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <TooltipProvider>
                                            <Tooltip v-if="user.roles?.[0]?.name !== RoleEnum.ADMIN">
                                                <TooltipTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-8 w-8"
                                                        @click="$inertia.visit(route('users.show', user.ulid))"
                                                    >
                                                        <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Ver detalles</p>
                                                </TooltipContent>
                                            </Tooltip>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="$inertia.visit(route('users.edit', user.ulid))">
                                                        <Icon icon="mdi:pencil-outline" class="w-4 h-4 text-blue-600" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Editar usuario</p>
                                                </TooltipContent>
                                            </Tooltip>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button 
                                                        variant="ghost" 
                                                        size="icon" 
                                                        class="h-8 w-8 hover:text-destructive"
                                                        @click="handleDelete(user)"
                                                    >
                                                        <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Eliminar usuario</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="5">
                                    <EmptyState
                                        icon="mdi:account-search-outline"
                                        title="No se encontraron usuarios"
                                        description="Intenta ajustar los filtros o la búsqueda para encontrar lo que buscas."
                                    />
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Cards view -->
        <div v-else-if="state.view === 'cards'" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            <UserCard v-for="user in users.data" :key="user.ulid" :user="user" />
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-4">
            <Pagination class="justify-center" :items-per-page="users.per_page" :total-items="users.total">
                <PaginationContent class="flex items-center space-x-5">
                    <PaginationItem :value="users.current_page">
                        <PaginationPrevious :disabled="users.current_page <= 1" @click="users.current_page > 1 && setPage(users.current_page - 1)" />
                    </PaginationItem>

                    <template v-for="(p, index) in pagesToShow" :key="index">
                        <PaginationItem v-if="typeof p === 'number'" :value="p" :is-active="p === users.current_page" @click="setPage(p)">
                            {{ p }}
                        </PaginationItem>
                        <PaginationItem v-else :value="0" disabled>
                            <PaginationEllipsis />
                        </PaginationItem>
                    </template>

                    <PaginationItem :value="users.current_page">
                        <PaginationNext
                            :disabled="users.current_page >= users.last_page"
                            @click="users.current_page < users.last_page && setPage(users.current_page + 1)"
                        />
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>

        <!-- Results info -->
        <div class="text-sm text-muted-foreground text-center">
            Mostrando {{ users.from ?? 0 }} a {{ users.to ?? 0 }} de {{ users.total }} usuarios
        </div>
    </div>
</template>
