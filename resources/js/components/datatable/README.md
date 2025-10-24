# DataTable Component - API Reference

## Overview

El DataTable es un componente genérico y reutilizable para mostrar datos tabulares con soporte para:
- Paginación server-side
- Búsqueda global
- Filtros personalizables
- Ordenamiento por columnas
- Vista responsive con cards
- Selección de filas
- Toggle de columnas visibles
- Sincronización con URL (Inertia.js)

**⚠️ IMPORTANTE:** Este componente NO debe modificarse. Para cada nuevo listado, crea componentes específicos que lo usen.

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [DataTable API](#datatable-api)
3. [Column API](#column-api)
4. [Slots](#slots)
5. [Events](#events)
6. [Complete Example](#complete-example)
7. [Do's and Don'ts](#dos-and-donts)
8. [Advanced Usage](#advanced-usage)

---

## Quick Start

### Minimal Example

```vue
<script setup lang="ts">
import DataTable from "@/components/datatable/Main.vue";
import Column from "@/components/datatable/Column.vue";
import type { FetcherResponse } from "@/types/FetcherResponse";
import type { User } from "@/types/User";

defineProps<{
    resource: FetcherResponse<User>;
}>();
</script>

<template>
    <DataTable 
        :resource="resource" 
        resourcePropName="users"
    >
        <Column header="Name">
            <template #body="slotProps">
                {{ slotProps.record.name }}
            </template>
        </Column>
        
        <Column header="Email">
            <template #body="slotProps">
                {{ slotProps.record.email }}
            </template>
        </Column>
    </DataTable>
</template>
```

---

## DataTable API

### Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `resource` | `Object` | ✅ Yes | - | FetcherResponse object from backend with pagination meta |
| `resourcePropName` | `String` | ✅ Yes | - | Name of the resource prop for Inertia `only` parameter |
| `refKey` | `String` | ❌ No | `""` | Unique identifier for multiple tables on same page |
| `keyRowsBy` | `String` | ❌ No | `"id"` | Field to use as key in v-for loops |
| `compact` | `Boolean` | ❌ No | `false` | Use compact table style with borders |
| `useFilters` | `Boolean` | ❌ No | `false` | Show filters button and panel |
| `useSearch` | `Boolean` | ❌ No | `true` | Show search bar |
| `usePagination` | `Boolean` | ❌ No | `true` | Show pagination controls |
| `selectableRows` | `Boolean` | ❌ No | `false` | Enable row selection with checkboxes |
| `selectedRows` | `Array` | ❌ No | `[]` | Array of selected rows (v-model) |
| `canToggleColumnsVisibility` | `Boolean` | ❌ No | `false` | Show column visibility toggle button |
| `visibleColumns` | `Array<string>` | ❌ No | `[]` | Array of visible column headers (v-model) |
| `highlightSelectedRows` | `Boolean` | ❌ No | `false` | Highlight selected rows with background color |
| `customDesign` | `Boolean` | ❌ No | `false` | Use custom design slot instead of table |
| `responsiveCards` | `String` | ❌ No | `""` | Breakpoint to switch to card view: 'sm', 'md', 'lg', 'xl' |
| `onBeforeFetch` | `Function` | ❌ No | `async (params) => params` | Transform params before fetch |
| `exportRoute` | `String` | ❌ No | `""` | Route for exporting data |
| `additionalExportOptions` | `Object` | ❌ No | - | Additional options for export |

### resource Structure

The `resource` prop must be a `FetcherResponse<T>` with the following structure:

```typescript
interface FetcherResponse<T> {
    current_page: number;        // Current page number (e.g., 2)
    data: Array<T>;              // Array of records
    first_page_url: string;      // URL to first page
    from: number;                // Index of first record shown (e.g., 13)
    last_page: number;           // Last page number (e.g., 12)
    last_page_url: string;       // URL to last page
    links: Array<{               // Pagination links
        active: boolean;
        label: string;
        url: string | null;
    }>;
    next_page_url: string | null;
    path: string;                // Base path (e.g., "/users")
    per_page: number;            // Items per page (e.g., 12)
    prev_page_url: string | null;
    to: number;                  // Index of last record shown (e.g., 24)
    total: number;               // Total number of records (e.g., 145)
}
```

**Required fields for DataTable to work:**
- `data` - Array of records
- `current_page` - Current page number
- `per_page` - Items per page
- `total` - Total number of records

---

## Column API

### Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `header` | `String` | ✅ Yes | - | Column header text |
| `field` | `String` | ❌ No | - | Field name for sorting (must match backend sortable fields) |
| `sortable` | `Boolean` | ❌ No | `false` | Enable sorting for this column |
| `togglable` | `Boolean` | ❌ No | `false` | Allow hiding/showing this column |
| `collapseAlways` | `Boolean` | ❌ No | `false` | Always hide this column (useful for mobile-only actions) |
| `headerClass` | `String` | ❌ No | `""` | CSS classes for `<th>` element |
| `avatar` | `Boolean` | ❌ No | `false` | (Legacy) Render avatar column |
| `clickable` | `Function` | ❌ No | `null` | (Legacy) Handler for clickable cells |
| `onEmptyText` | `String` | ❌ No | - | Text to show when cell value is empty |

### Slots

#### #body

Custom cell content. Receives `slotProps` with the record data.

```vue
<Column header="Name" field="name" :sortable="true">
    <template #body="slotProps">
        <strong>{{ slotProps.record.name }}</strong>
    </template>
</Column>
```

**slotProps structure:**
```typescript
{
    record: T;  // Full record object (e.g., User)
}
```

---

## Slots

### #filters

Custom filters panel. Content is rendered inside a Popover.

```vue
<DataTable :resource="users" use-filters>
    <template #filters>
        <UserFiltros v-model:filtros="filtros" />
    </template>
</DataTable>
```

**How it works:**
1. Filters component uses `v-model` to bind filter values
2. DataTable listens for changes via `provide/inject` pattern
3. On change, DataTable automatically calls `router.get()` with filter params
4. URL is updated with `filters[field]=value` query params

**Filter component pattern:**

```vue
<script setup lang="ts">
export interface FiltrosUser {
    role: string | null;
}

const filtros = defineModel<FiltrosUser>('filtros', { 
    default: { role: null }
});
</script>
```

**Required provide in Table Service:**

```typescript
provide("users_filters", computed(() => ({
    role: this.filtros.value.role,
})));
```

### #responsive-card

Custom card view for mobile/tablet. Receives `slotProps` with the record.

```vue
<DataTable :resource="users" :responsiveCards="'lg'">
    <template #responsive-card="{ slotProps }">
        <Card>
            <CardHeader>
                <h3>{{ slotProps.name }}</h3>
            </CardHeader>
            <CardContent>
                <p>{{ slotProps.email }}</p>
            </CardContent>
        </Card>
    </template>
</DataTable>
```

**slotProps:** Full record object (e.g., `User`)

**Breakpoints:**
- `'sm'` - 480px
- `'md'` - 768px
- `'lg'` - 976px (recommended)
- `'xl'` - 1440px

### #searchbar

Replace default search bar.

```vue
<template #searchbar>
    <CustomSearchBar @search="handleSearch" />
</template>
```

### #before-actions / #after-actions

Insert content before/after controls (filters, column toggle).

```vue
<template #before-actions>
    <Button @click="export">Export</Button>
</template>
```

### #before-table / #after-table

Insert content before/after the table.

```vue
<template #before-table>
    <Alert>Important message</Alert>
</template>
```

### #custom-design

Replace the entire table with custom design. Requires `customDesign` prop.

```vue
<DataTable :resource="users" :customDesign="true">
    <template #custom-design="{ slotProps }">
        <div v-for="user in slotProps" :key="user.id">
            <!-- Custom layout -->
        </div>
    </template>
</DataTable>
```

---

## Events

### Internal Events (Handled Automatically)

These events are emitted internally and handled by the DataTable component:

| Event | Payload | Description |
|-------|---------|-------------|
| `sort-change` | `{ column: string, direction: 'asc'\|'desc' }` | Column sort changed |
| `page-change` | `{ page: number }` | Page changed |
| `per-page-change` | `{ offset: number }` | Items per page changed |

**Note:** You don't need to handle these events. DataTable automatically calls `router.get()` with updated query params.

### External Events (v-model)

| Event | Payload | Description |
|-------|---------|-------------|
| `update:visibleColumns` | `Array<string>` | Visible columns changed |
| `update:selectedRows` | `Array<T>` | Selected rows changed |

**Usage:**

```vue
<script setup lang="ts">
const visibleColumns = ref(['Name', 'Email', 'Actions']);
const selectedRows = ref([]);
</script>

<template>
    <DataTable
        v-model:visibleColumns="visibleColumns"
        v-model:selectedRows="selectedRows"
    />
</template>
```

---

## Complete Example

Based on the Users implementation:

### Backend (UserService.php)

```php
<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function indexFetch(): LengthAwarePaginator
    {
        $users = User::query()
            ->with(['roles', 'nanny', 'tutor'])
            ->orderBy('created_at', 'desc');

        $sortables = ['email', 'name', 'surnames'];
        $searchables = ['email', 'name', 'surnames'];

        return Fetcher::for($users)
            ->allowFilters([
                'role' => [
                    'using' => fn (Filter $filter) => $filter->usingScope('filtrarPorRole'),
                ],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(12);
    }
}
```

### Frontend (UserTable.vue)

```vue
<script setup lang="ts">
import DataTable from "@/components/datatable/Main.vue";
import Column from "@/components/datatable/Column.vue";
import type { FetcherResponse } from "@/types/FetcherResponse";
import type { User } from "@/types/User";
import UserCard from "./UserCard.vue";
import UserFiltros from "./UserFiltros.vue";
import { UserTableService } from "@/services/userTableService";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import Badge from "@/components/common/Badge.vue";
import DeleteModal from '@/components/common/DeleteModal.vue';

defineProps<{
    resource: FetcherResponse<User>;
    roles: Array<string>;
}>();

const {
    usuarioEliminar,
    modalEliminarUsuario,
    filtros,
    visibleColumns,
    verUsuarioPerfil,
    editarUsuario,
    abrirModalEliminarUsuario,
    cerrarModalEliminarUsuario,
    eliminarUsuario,
    getRoleBadgeClasses,
} = new UserTableService();
</script>

<template>
    <DataTable
        :resource="resource"
        resourcePropName="users"
        use-filters
        :canToggleColumnsVisibility="true"
        v-model:visibleColumns="visibleColumns"
        :responsiveCards="'lg'"
    >
        <!-- Filters Panel -->
        <template #filters>
            <UserFiltros v-model:filtros="filtros" :roles="roles" />
        </template>

        <!-- Mobile Card View -->
        <template #responsive-card="{ slotProps }">
            <UserCard :user="slotProps"/>
        </template>

        <!-- Profile Column with Avatar -->
        <Column header="Perfil" field="id">
            <template #body="slotProps">
                <div
                    @click="verUsuarioPerfil(slotProps.record)"
                    class="flex items-center gap-2 cursor-pointer hover:text-rose-400"
                >
                    <Avatar shape="square" size="sm">
                        <AvatarFallback>
                            {{ getUserInitials(slotProps.record) }}
                        </AvatarFallback>
                    </Avatar>
                </div>
            </template>
        </Column>

        <!-- Name Column (Sortable) -->
        <Column header="Nombre" field="name" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.name ?? "—" }}
            </template>
        </Column>

        <!-- Surnames Column (Sortable) -->
        <Column header="Apellidos" field="surnames" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.surnames ?? "—" }}
            </template>
        </Column>

        <!-- Email Column (Sortable) -->
        <Column header="Correo Electrónico" field="email" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.email ?? "—" }}
            </template>
        </Column>

        <!-- Role Column with Badge -->
        <Column header="Rol">
            <template #body="slotProps">
                <Badge
                    class="min-w-32"
                    :label="getRoleLabelByString(slotProps.record?.roles?.[0]?.name ?? '') || 'Sin rol'"
                    :customClass="getRoleBadgeClasses(slotProps.record?.roles?.[0]?.name ?? '')"
                />
            </template>
        </Column>

        <!-- Actions Column -->
        <Column header="Acciones" field="id">
            <template #body="slotProps">
                <div class="grid grid-cols-3 gap-2">
                    <div
                        @click="editarUsuario(slotProps.record)"
                        class="flex justify-center items-center text-blue-600 hover:cursor-pointer"
                        title="Editar usuario"
                    >
                        <Icon icon="mdi:edit-outline" :size="20" />
                    </div>

                    <div
                        @click="abrirModalEliminarUsuario(slotProps.record)"
                        class="flex justify-center items-center text-rose-600 hover:cursor-pointer"
                        title="Eliminar usuario"
                    >
                        <Icon icon="fluent:delete-12-regular" :size="20" />
                    </div>
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- Delete Confirmation Modal -->
    <DeleteModal
        v-model:show="modalEliminarUsuario"
        :message="`¿Estás seguro de eliminar a ${usuarioEliminar?.name ?? ''} (${usuarioEliminar?.email ?? ''})?`"
        :onConfirm="eliminarUsuario"
        :onCancel="cerrarModalEliminarUsuario"
        confirmText="Sí, eliminar"
        cancelText="No, cancelar"
    />
</template>
```

### Table Service (userTableService.ts)

```typescript
import { computed, provide, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { User } from "@/types/User";
import { RoleEnum } from "@/enums/role.enum";
import { FiltrosUser } from "@/Pages/User/components/UserFiltros.vue";

export class UserTableService {
    public usuarioEliminar = ref<User|null>(null);
    public modalEliminarUsuario = ref(false);

    public filtros = ref<FiltrosUser>({
        role: null,
    });

    public visibleColumns = ref<Array<string>>([
        'Perfil',
        'Nombre',
        'Apellidos',
        'Correo Electrónico',
        'Acciones', 
        'Rol',
    ]);

    public constructor() {
        provide("users_filters", computed(() => this.getFilters()));
        provide("clear_users_filters", () => {
            this.filtros.value = { role: null };
        });
    }

    public getFilters = () => ({
        role: this.filtros.value.role,
    });

    public verUsuarioPerfil = (user: User) => {
        router.get(route('users.show', user.ulid));
    }

    public editarUsuario = (user: User) => {
        router.get(route('users.edit', user.ulid));
    };

    public abrirModalEliminarUsuario = (user: User) => {
        this.usuarioEliminar.value = user;
        this.modalEliminarUsuario.value = true;
    };

    public cerrarModalEliminarUsuario = () => {
        this.modalEliminarUsuario.value = false;
    };

    public eliminarUsuario = () => {
        router.delete(route('users.destroy', this.usuarioEliminar.value?.ulid));
        this.modalEliminarUsuario.value = false;
    };

    public getRoleBadgeClasses = (role: RoleEnum): string => {
        const classes: Record<RoleEnum, string> = {
            [RoleEnum.ADMIN]: 'bg-emerald-200/70 text-emerald-500',
            [RoleEnum.NANNY]: 'bg-pink-200/70 text-pink-500',
            [RoleEnum.TUTOR]: 'bg-sky-200/70 text-sky-500',
        };
        return classes[role] ?? '';
    }
}
```

---

## Do's and Don'ts

### ✅ DO

- **DO** create module-specific Table components (e.g., `UserTable.vue`, `NannyTable.vue`)
- **DO** use the `#body` slot for custom cell rendering
- **DO** match `Column.field` with backend sortable fields
- **DO** provide the `#responsive-card` slot for better mobile UX
- **DO** use a Table Service class for actions and state management
- **DO** use `v-model` for `visibleColumns` and `filtros`
- **DO** validate sortable fields on the backend (whitelist)
- **DO** provide meaningful fallbacks for empty values (`?? "—"`)
- **DO** use `resourcePropName` that matches the Inertia prop name
- **DO** eager load relationships in the backend to prevent N+1 queries

### ❌ DON'T

- **DON'T** modify files in `resources/js/components/datatable/*`
- **DON'T** use client-side sorting/filtering if backend handles it
- **DON'T** forget to set `:sortable="true"` on sortable columns
- **DON'T** use `field` names that don't exist in backend `$sortables`
- **DON'T** pass unsanitized data directly to the DataTable
- **DON'T** forget to handle empty states in `#body` slots
- **DON'T** use `resource.data` directly - let DataTable handle it
- **DON'T** forget to provide `resourcePropName` for Inertia partial reloads
- **DON'T** mix multiple query params systems - use DataTable's built-in handling

---

## Advanced Usage

### Multiple Tables on Same Page

Use `refKey` to differentiate tables:

```vue
<DataTable 
    :resource="users" 
    resourcePropName="users"
    refKey="active-users"
/>

<DataTable 
    :resource="inactiveUsers" 
    resourcePropName="inactiveUsers"
    refKey="inactive-users"
/>
```

This will generate separate query params:
- `active-users-searchTerm`, `active-users-page`
- `inactive-users-searchTerm`, `inactive-users-page`

### Custom Empty State

```vue
<DataTable :resource="users">
    <template #before-table>
        <div v-if="!users.data.length" class="text-center py-12">
            <Icon icon="mdi:inbox" class="w-16 h-16 mb-4" />
            <p>No users found</p>
            <Button @click="clearFilters">Clear Filters</Button>
        </div>
    </template>
</DataTable>
```

### Selectable Rows

```vue
<script setup lang="ts">
const selectedRows = ref<User[]>([]);

function bulkDelete() {
    if (confirm(`Delete ${selectedRows.value.length} users?`)) {
        router.post(route('users.bulk-delete'), {
            ids: selectedRows.value.map(u => u.id)
        });
    }
}
</script>

<template>
    <div>
        <Button 
            v-if="selectedRows.length" 
            @click="bulkDelete"
        >
            Delete {{ selectedRows.length }} users
        </Button>
        
        <DataTable
            :resource="users"
            :selectableRows="true"
            v-model:selectedRows="selectedRows"
        />
    </div>
</template>
```

### Column Visibility with Persistence

```typescript
import { useLocalStorage } from '@vueuse/core';

export class UserTableService {
    public visibleColumns = useLocalStorage<Array<string>>(
        'user-table-visible-columns',
        ['Perfil', 'Nombre', 'Email', 'Acciones']
    );
}
```

### Transform Params Before Fetch

```vue
<DataTable
    :resource="users"
    :onBeforeFetch="transformParams"
/>

<script>
async function transformParams(params) {
    // Add additional params
    return {
        ...params,
        include_deleted: showDeleted.value,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    };
}
</script>
```

### Nested Field Sorting

For sorting by nested fields (e.g., `user.name` in Nanny table):

**Backend:**
```php
$sortables = ['user.name', 'experiencia', 'tarifa'];

// Fetcher handles nested sorting automatically via QueryHelpers
```

**Frontend:**
```vue
<Column header="Nombre" field="user.name" :sortable="true">
    <template #body="slotProps">
        {{ slotProps.record?.user?.name ?? "—" }}
    </template>
</Column>
```

---

## Troubleshooting

See the main pattern documentation at `docs/datatable/patron-index-listados.md` section 9 for detailed troubleshooting.

Common issues:
- **Filters not working:** Check `provide/inject` setup
- **Sorting not working:** Verify `field` matches backend `$sortables`
- **Pagination not working:** Ensure backend returns `LengthAwarePaginator`
- **Cards not showing:** Provide `#responsive-card` slot and set `responsiveCards` prop

---

## Related Files

- **Pattern Documentation:** `docs/datatable/patron-index-listados.md`
- **Checklist:** `docs/datatable/checklist-nuevo-listado.md`
- **Main Component:** `resources/js/components/datatable/Main.vue`
- **Column Component:** `resources/js/components/datatable/Column.vue`
- **Fetcher Class:** `app/Classes/Fetcher/Fetcher.php`

---

## Support

For questions or issues, refer to:
1. Pattern documentation: `docs/datatable/patron-index-listados.md`
2. Troubleshooting section in this README
3. Example implementation: `resources/js/Pages/User/components/UserTable.vue`
