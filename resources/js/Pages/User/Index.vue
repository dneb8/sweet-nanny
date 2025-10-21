<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { User } from '@/types/User';
import Heading from '@/components/Heading.vue';
import DataTable, { type DataTableColumn } from '@/components/datatable/DataTable.vue';
import UserCard from '@/Pages/User/partials/UserCard.vue';
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';
import { Badge } from '@/components/ui/badge';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum';

const props = defineProps<{
    users: FetcherResponse<User>;
    roles: Array<string>;
    searchables: string[];
    sortables: string[];
}>();

// Get current URL params for initial state
const urlParams = new URLSearchParams(window.location.search);
const initialSearch = urlParams.get('search') || '';
const initialSort = urlParams.get('sort') || null;
const initialDir = (urlParams.get('dir') as 'asc' | 'desc' | null) || null;

// Define columns for the DataTable
const columns = computed<DataTableColumn<User>[]>(() => [
    {
        id: 'name',
        header: 'Nombre',
        accessorKey: 'name',
        sortable: true,
    },
    {
        id: 'email',
        header: 'Email',
        accessorKey: 'email',
        sortable: true,
    },
    {
        id: 'role',
        header: 'Rol',
        sortable: false,
        cell: (row: User) => row.roles?.[0]?.name ?? 'Sin rol',
    },
    {
        id: 'created_at',
        header: 'Fecha de creación',
        accessorKey: 'created_at',
        sortable: true,
    },
]);

// Format date
function formatDate(date: string | null): string {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
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

// Get current URL params
function getCurrentParams() {
    const params = new URLSearchParams(window.location.search);
    const result: Record<string, string> = {};
    params.forEach((value, key) => {
        result[key] = value;
    });
    return result;
}

// Handle search
function handleSearch(value: string) {
    const params = getCurrentParams();
    router.get(
        route('users.index'),
        {
            ...params,
            search: value || undefined,
            page: undefined, // Reset to first page
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

// Handle sort change
function handleSortChange({ id, direction }: { id: string; direction: 'asc' | 'desc' | null }) {
    const params = getCurrentParams();
    router.get(
        route('users.index'),
        {
            ...params,
            sort: direction ? id : undefined,
            dir: direction || undefined,
            page: undefined, // Reset to first page
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

// Handle pagination
function handleGoto(url: string) {
    router.get(url, {}, { preserveState: true, preserveScroll: true });
}

// Handle delete
function handleDelete(user: User) {
    if (confirm(`¿Estás seguro de que deseas eliminar a ${user.name} ${user.surnames}? Esta acción no se puede deshacer.`)) {
        router.delete(route('users.destroy', user.ulid));
    }
}
</script>

<template>
    <Head title="Usuarios" />
    <div class="flex flex-row justify-between mb-4">
        <Heading icon="proicons:person-multiple" title="Listado de Usuarios" />
        <Link :href="route('users.create')">
            <Button>
                <Icon icon="ri:user-add-line" width="24" height="24" />
                Crear Usuario
            </Button>
        </Link>
    </div>

    <DataTable
        :columns="columns"
        :data="users.data"
        :links="{
            prev: users.prev_page_url,
            next: users.next_page_url,
        }"
        :page="users.current_page"
        :per-page="users.per_page"
        :total="users.total"
        :last-page="users.last_page"
        :card-slot="true"
        :search-query="initialSearch"
        :sort-by="initialSort"
        :sort-dir="initialDir"
        @search="handleSearch"
        @sort:change="handleSortChange"
        @goto="handleGoto"
    >
        <!-- Custom cell for name with verified icon -->
        <template #cell-name="{ row }">
            <div class="flex items-center gap-2">
                <span>{{ row.name }} {{ row.surnames }}</span>
                <Icon v-if="row.email_verified_at" icon="mdi:check-decagram" class="w-4 h-4 text-emerald-500" />
            </div>
        </template>

        <!-- Custom cell for role with badge -->
        <template #cell-role="{ row }">
            <Badge :variant="getRoleBadgeVariant(row.roles?.[0]?.name ?? '')">
                {{ getRoleLabelByString(row.roles?.[0]?.name ?? '') ?? 'Sin rol' }}
            </Badge>
        </template>

        <!-- Custom cell for created_at with formatted date -->
        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <!-- Actions slot -->
        <template #actions="{ row }">
            <div class="flex justify-end gap-1">
                <TooltipProvider>
                    <Tooltip v-if="row.roles?.[0]?.name !== RoleEnum.ADMIN">
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="$inertia.visit(route('users.show', row.ulid))">
                                <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Ver detalles</p>
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="$inertia.visit(route('users.edit', row.ulid))">
                                <Icon icon="mdi:pencil-outline" class="w-4 h-4 text-blue-600" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Editar usuario</p>
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-8 w-8 hover:text-destructive" @click="handleDelete(row)">
                                <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Eliminar usuario</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </template>

        <!-- Card view for mobile -->
        <template #card="{ row }">
            <UserCard :user="row" />
        </template>

        <!-- Empty state -->
        <template #empty>
            <div class="flex flex-col items-center justify-center py-8">
                <Icon icon="mdi:account-search-outline" class="w-16 h-16 text-muted-foreground mb-2" />
                <p class="text-lg font-semibold">No se encontraron usuarios</p>
                <p class="text-sm text-muted-foreground">Intenta ajustar los filtros o la búsqueda para encontrar lo que buscas.</p>
            </div>
        </template>
    </DataTable>
</template>
