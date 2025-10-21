# DataTable Component

Componente reutilizable para mostrar datos tabulares con funcionalidades backend.

## Características

- ✅ Búsqueda con botón (no realtime)
- ✅ Paginación controlada por backend
- ✅ Ordenamiento opcional por columna
- ✅ Menú de columnas (mostrar/ocultar)
- ✅ Estilos personalizables por columna
- ✅ Vista responsive (tabla → cards en móvil)
- ✅ Slots para personalización
- ✅ Sin columna de checkboxes

## Uso básico

```vue
<script setup lang="ts">
import DataTable, { type DataTableColumn } from '@/components/datatable/DataTable.vue';
import type { User } from '@/types/User';

const columns: DataTableColumn<User>[] = [
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
];

const data = [
  { name: 'Juan', email: 'juan@example.com' },
  { name: 'María', email: 'maria@example.com' },
];
</script>

<template>
  <DataTable
    :columns="columns"
    :data="data"
  />
</template>
```

## Props

### Requeridos
- `columns`: Array de columnas
- `data`: Array de datos

### Opcionales (para paginación backend)
- `links`: `{ prev?: string | null, next?: string | null }` - URLs de navegación
- `page`: Número de página actual (default: 1)
- `perPage`: Elementos por página (default: 15)
- `total`: Total de elementos (default: 0)
- `lastPage`: Última página (default: 1)
- `cardSlot`: Habilitar vista de cards en móvil (default: false)
- `sortBy`: Columna inicial de ordenamiento (default: null)
- `sortDir`: Dirección inicial de ordenamiento: 'asc' | 'desc' (default: null)
- `searchQuery`: Valor inicial del campo de búsqueda (default: '')

## Columnas

```typescript
interface DataTableColumn<T> {
  id: string;                    // Identificador único
  header: string;                // Texto del encabezado
  accessorKey?: keyof T;         // Llave para acceder al valor
  cell?: (row: T) => any;        // Función custom para renderizar
  sortable?: boolean;            // Si la columna es ordenable
  headerClass?: string;          // Clases Tailwind para <th>
  cellClass?: string;            // Clases Tailwind para <td>
}
```

## Eventos

### `@search`
Emitido al hacer click en el botón de búsqueda.
```typescript
(value: string) => void
```

### `@sort:change`
Emitido al hacer click en una columna sortable.
```typescript
({ id: string, direction: 'asc' | 'desc' | null }) => void
```

### `@goto`
Emitido al cambiar de página.
```typescript
(url: string) => void
```

### `@change:perPage`
Emitido al cambiar elementos por página.
```typescript
(perPage: number) => void
```

## Slots

### Named Slots

#### `#cell-{columnId}`
Personalizar contenido de una celda específica.
```vue
<template #cell-name="{ row, value }">
  <strong>{{ value }}</strong>
</template>
```

#### `#actions`
Columna de acciones (se agrega automáticamente si el slot existe).
```vue
<template #actions="{ row }">
  <Button @click="edit(row)">Editar</Button>
</template>
```

#### `#card`
Vista de tarjeta para móvil (requiere `:card-slot="true"`).
```vue
<template #card="{ row }">
  <CustomCard :data="row" />
</template>
```

#### `#empty`
Estado vacío personalizado.
```vue
<template #empty>
  <div>No hay datos</div>
</template>
```

#### `#controls`
Controles adicionales junto al buscador.
```vue
<template #controls>
  <Button>Filtros</Button>
</template>
```

## Ejemplo completo con backend

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import DataTable, { type DataTableColumn } from '@/components/datatable/DataTable.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { User } from '@/types/User';

const props = defineProps<{
  users: FetcherResponse<User>;
}>();

// Get current URL params for initial state
const urlParams = new URLSearchParams(window.location.search);
const initialSearch = urlParams.get('search') || '';
const initialSort = urlParams.get('sort') || null;
const initialDir = (urlParams.get('dir') as 'asc' | 'desc' | null) || null;

const columns: DataTableColumn<User>[] = [
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
];

function getCurrentParams() {
  const params = new URLSearchParams(window.location.search);
  const result: Record<string, string> = {};
  params.forEach((value, key) => {
    result[key] = value;
  });
  return result;
}

function handleSearch(value: string) {
  const params = getCurrentParams();
  router.get(route('users.index'), {
    ...params,
    search: value || undefined,
    page: undefined, // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}

function handleSortChange({ id, direction }) {
  const params = getCurrentParams();
  router.get(route('users.index'), {
    ...params,
    sort: direction ? id : undefined,
    dir: direction || undefined,
    page: undefined, // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}

function handleGoto(url: string) {
  router.get(url, {}, { preserveState: true, preserveScroll: true });
}
</script>

<template>
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
    :search-query="initialSearch"
    :sort-by="initialSort"
    :sort-dir="initialDir"
    @search="handleSearch"
    @sort:change="handleSortChange"
    @goto="handleGoto"
  >
    <template #actions="{ row }">
      <Button @click="edit(row)">Editar</Button>
    </template>
  </DataTable>
</template>
```

## Responsiveness

El componente detecta automáticamente el viewport:
- **Desktop (≥ 768px)**: Vista de tabla
- **Mobile (< 768px)**: Vista de tabla o cards (si `card-slot` está habilitado y existe slot `#card`)

## Estilos personalizados

```typescript
const columns: DataTableColumn<User>[] = [
  {
    id: 'status',
    header: 'Estado',
    accessorKey: 'status',
    headerClass: 'text-right',
    cellClass: 'font-bold text-right',
  },
];
```
