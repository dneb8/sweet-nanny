# DataTable Components Documentation

> **Last Updated:** 2025-10-24  
> **Author:** Sweet Nanny Development Team  
> **Version:** 1.0.0

## 📋 Índice

1. [Resumen](#resumen)
2. [Arquitectura](#arquitectura)
3. [Componentes](#componentes)
   - [DataTable.vue](#datatablevue)
   - [CardList.vue](#cardlistvue)
   - [Columns.ts](#columnsts)
4. [Tipos y Contratos](#tipos-y-contratos)
5. [Integración con Backend](#integración-con-backend)
6. [Ejemplos Completos](#ejemplos-completos)
7. [Patrones de Filtros](#patrones-de-filtros)
8. [Buenas Prácticas](#buenas-prácticas)
9. [Troubleshooting](#troubleshooting)
10. [Extensiones Futuras](#extensiones-futuras)

---

## Resumen

Este directorio contiene los componentes reutilizables para visualizar datos en **tablas** y **listas de tarjetas responsivas**. El sistema proporciona dos patrones principales:

1. **DataTable.vue** - Tabla tradicional basada en [@tanstack/vue-table](https://tanstack.com/table) para visualización tabular
2. **CardList.vue** - Lista de tarjetas responsiva con filtrado y paginación del lado del cliente

Ambos componentes están diseñados para trabajar con el backend de Laravel usando el patrón `Fetcher` y el tipo `FetcherResponse<T>`.

---

## Arquitectura

### Estructura de Archivos

```
resources/js/components/datatable/
├── README.md          # Este archivo
├── DataTable.vue      # Componente de tabla TanStack
├── CardList.vue       # Componente de lista de tarjetas
└── Columns.ts         # Helper para generar columnas automáticamente
```

### Diagrama de Flujo de Datos

```
┌──────────────┐
│   Backend    │
│  (Laravel)   │
└──────┬───────┘
       │ Fetcher::for($query)->paginate()
       ↓
┌──────────────────────┐
│ FetcherResponse<T>   │
│  - data: T[]         │
│  - current_page      │
│  - per_page          │
│  - total             │
│  - links             │
└──────┬───────────────┘
       │
       ├─────────────────────┐
       ↓                     ↓
┌──────────────┐    ┌────────────────┐
│ DataTable    │    │   CardList     │
│ (TanStack)   │    │ (Custom Cards) │
└──────────────┘    └────────────────┘
```

---

## Componentes

### DataTable.vue

Componente de tabla tradicional basado en **@tanstack/vue-table** (TanStack Table v8).

#### Props

| Prop | Tipo | Requerido | Default | Descripción |
|------|------|-----------|---------|-------------|
| `columns` | `ColumnDef<TData, TValue>[]` | ❌ | `[]` | Definiciones de columnas (TanStack format) |
| `data` | `TData[]` | ✅ | - | Array de datos a mostrar |

#### Características

- **Auto-generación de columnas**: Si no se proporcionan columnas, se generan automáticamente desde los datos
- **Tabla responsive**: Usa componentes shadcn-vue (`Table`, `TableHeader`, `TableBody`, etc.)
- **TanStack Table**: Aprovecha todas las capacidades de TanStack Table (sorting, filtering, etc.)
- **Empty state**: Muestra "No results." cuando no hay datos

#### Ejemplo de Uso Básico

```vue
<script setup lang="ts">
import DataTable from '@/components/datatable/DataTable.vue'
import type { User } from '@/types/User'
import { generateColumns } from '@/components/datatable/Columns'

const props = defineProps<{
  resource: User[]
}>()

// Opción 1: Columnas automáticas
const columns = generateColumns(props.resource)

// Opción 2: Columnas manuales (para mayor control)
const manualColumns = [
  {
    accessorKey: 'name',
    header: 'Nombre',
    cell: ({ row }) => row.getValue('name'),
  },
  {
    accessorKey: 'email',
    header: 'Correo Electrónico',
    cell: ({ row }) => row.getValue('email'),
  },
]
</script>

<template>
  <DataTable :data="resource" :columns="columns" />
</template>
```

#### Ejemplo con Definición Manual de Columnas

```vue
<script setup lang="ts">
import DataTable from '@/components/datatable/DataTable.vue'
import type { ColumnDef } from '@tanstack/vue-table'
import type { User } from '@/types/User'
import { Badge } from '@/components/ui/badge'

const props = defineProps<{
  users: User[]
}>()

const columns: ColumnDef<User>[] = [
  {
    accessorKey: 'name',
    header: 'Nombre',
    cell: ({ row }) => {
      const user = row.original
      return `${user.name} ${user.surnames}`
    },
  },
  {
    accessorKey: 'email',
    header: 'Email',
  },
  {
    accessorKey: 'roles',
    header: 'Rol',
    cell: ({ row }) => {
      const role = row.original.roles?.[0]?.name
      return h(Badge, { class: getRoleBadgeClass(role) }, () => role)
    },
  },
]
</script>

<template>
  <DataTable :data="users" :columns="columns" />
</template>
```

---

### CardList.vue

Componente de lista de tarjetas con **filtrado y paginación del lado del cliente**. Ideal para vistas responsivas tipo "grid".

#### Props

| Prop | Tipo | Requerido | Default | Descripción |
|------|------|-----------|---------|-------------|
| `items` | `any[]` | ✅ | - | Array de items a mostrar |
| `searchables` | `string[]` | ✅ | - | Campos por los cuales buscar globalmente |
| `sortables` | `string[]` | ✅ | - | Campos disponibles para filtros avanzados |
| `FilterPanel` | `object` | ✅ | - | Componente de panel de filtros |
| `perPage` | `number` | ❌ | `12` | Items por página |

#### Slots

| Slot | Props | Descripción |
|------|-------|-------------|
| `default` | `{ item }` | Slot principal para renderizar cada tarjeta |

#### Características

- **Búsqueda global**: Campo de búsqueda que filtra por los campos en `searchables`
- **Filtros personalizados**: Panel de filtros configurable via prop `FilterPanel`
- **Paginación del cliente**: Calcula automáticamente las páginas basándose en `perPage`
- **Responsive**: Grid adaptativo (1 col móvil → 2 cols tablet → 3 cols desktop)
- **Popover de filtros**: Los filtros aparecen en un popover en móvil, inline en desktop

#### Ejemplo de Uso con UserCard

```vue
<script setup lang="ts">
import CardList from '@/components/datatable/CardList.vue'
import UserCard from './partials/UserCard.vue'
import UserFilters from './partials/UserFilters.vue'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'

const props = defineProps<{
  users: FetcherResponse<User>
  searchables: string[]
  sortables: string[]
}>()
</script>

<template>
  <CardList
    :items="users.data"
    :per-page="9"
    :sortables="sortables"
    :searchables="searchables"
    :FilterPanel="UserFilters"
  >
    <template #default="{ item }">
      <UserCard :user="item" />
    </template>
  </CardList>
</template>
```

#### Flujo Interno de CardList

1. **Búsqueda Global** (`searchedItems`): Filtra por campos en `searchables` usando `String.toLowerCase().includes()`
2. **Filtros Personalizados** (`filteredItems`): Aplica la función de filtro del `FilterPanel`
3. **Paginación** (`paginatedItems`): Divide los resultados filtrados en páginas
4. **Reset de Página**: Cuando cambian los filtros, la página actual vuelve a 1

---

### Columns.ts

Helper para generar definiciones de columnas automáticamente basándose en las claves del primer objeto de datos.

#### API

```typescript
function generateColumns<TData extends Record<string, any>>(
  data: TData[]
): ColumnDef<TData, any>[]
```

#### Comportamiento

- Si `data` está vacío, retorna `[]`
- Para cada clave en `data[0]`:
  - `accessorKey`: La clave misma (ej: `"name"`)
  - `header`: La clave capitalizada (ej: `"Name"`)
  - `cell`: Renderiza el valor de la celda usando `row.getValue(key)`

#### Ejemplo

```typescript
import { generateColumns } from '@/components/datatable/Columns'

const users = [
  { name: 'Juan', email: 'juan@example.com' },
  { name: 'María', email: 'maria@example.com' },
]

const columns = generateColumns(users)
// Genera:
// [
//   { accessorKey: 'name', header: 'Name', cell: ... },
//   { accessorKey: 'email', header: 'Email', cell: ... }
// ]
```

---

## Tipos y Contratos

### FetcherResponse\<T\>

Interface para la respuesta paginada del backend (formato Laravel Paginator).

```typescript
// resources/js/types/FetcherResponse.d.ts
export interface FetcherResponse<T> {
    current_page: number;
    data: Array<T>;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: {
        active: boolean;
        label: string;
        url: string | null;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}
```

### User Type (Ejemplo)

```typescript
// resources/js/types/User.d.ts
import Nanny from "./Nanny";
import { Rol } from "./Rol";

export interface User {
  ulid: string;
  name: string;
  surnames: string;
  email: string;
  email_verified_at: date;
  number: string;
  roles: Array<Rol>;
  tutor?: Tutor; 
  nanny?: Nanny; 
}
```

---

## Integración con Backend

### Backend: Fetcher Class

El backend usa la clase `Fetcher` para construir queries con paginación flexible.

```php
// app/Classes/Fetcher/Fetcher.php
use App\Classes\Fetcher\Fetcher;

// En el servicio
public function indexFetch(): LengthAwarePaginator
{
    $users = User::query()->orderBy('created_at', 'desc');

    return Fetcher::for($users)
        ->paginate(10); // Default: 10 items por página
}
```

#### Parámetros de Query Soportados

| Parámetro | Ejemplo | Descripción |
|-----------|---------|-------------|
| `per_page` | `?per_page=20` | Items por página (o `'all'` para todos) |
| `page` | `?page=2` | Número de página actual |

### Backend: UserController Example

```php
// app/Http/Controllers/UserController.php
public function index(UserService $userService): Response
{
    $sortables = ['role', 'email_verified_at'];
    $searchables = ['name', 'email', 'surnames'];
    $users = $userService->indexFetch();

    return Inertia::render('User/Index', [
        'users' => $users,
        'roles' => RoleEnum::cases(),
        'sortables' => $sortables,
        'searchables' => $searchables,
    ]);
}
```

**Notas importantes:**
- `sortables`: Lista de campos que pueden usarse en filtros (pasado al FilterPanel)
- `searchables`: Lista de campos para búsqueda global (usado en CardList)
- Los datos vienen en `users.data` (estructura de `FetcherResponse`)

---

## Ejemplos Completos

### Ejemplo 1: Tabla Simple con DataTable

**Página:** `resources/js/Pages/User/partials/UserTable.vue`

```vue
<script lang="ts" setup>
import DataTable from '@/components/datatable/DataTable.vue'
import type { User } from '@/types/User'
import { generateColumns } from '@/components/datatable/Columns'

const props = defineProps<{
  resource: User[]
}>()

const columns = generateColumns(props.resource)
</script>

<template>
  <DataTable :data="resource" :columns="columns" />
</template>
```

### Ejemplo 2: CardList con Filtros

**Página:** `resources/js/Pages/User/Index.vue`

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'
import CardList from '@/components/datatable/CardList.vue'
import UserCard from './partials/UserCard.vue'
import UserFilters from './partials/UserFilters.vue'
import { Button } from '@/components/ui/button'
import Heading from '@/components/Heading.vue'

const props = defineProps<{
    users: FetcherResponse<User>
    roles: Array<string>
    searchables: string[]
    sortables: string[]
}>()
</script>

<template>
  <Head title="Usuarios" />
  
  <div class="flex flex-row justify-between mb-4">
    <Heading icon='proicons:person-multiple' title="Listado de Usuarios"/>
    <Link :href="route('users.create')">
      <Button> 
        <Icon icon="ri:user-add-line" width="48" height="48" />
        Crear Usuario
      </Button>
    </Link>
  </div>

  <CardList
    :items="users.data"
    :per-page="9"
    :sortables="sortables"
    :searchables="searchables"
    :FilterPanel="UserFilters"
  >
    <template #default="{ item }">
      <UserCard :user="item" />
    </template>
  </CardList>
</template>
```

### Ejemplo 3: UserCard Component

**Card Component:** `resources/js/Pages/User/partials/UserCard.vue`

```vue
<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card'
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import type { User } from '@/types/User'
import { useUserService } from '@/services/UserService'
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum'

const props = defineProps<{
  user: User
}>()

const {
  showDeleteModal,
  showUser,
  editUser,
  deleteUser,
  confirmDeleteUser,
  getRoleBadgeClass,
} = useUserService(props.user)
</script>

<template>
  <Card class="relative overflow-hidden">
    <!-- Acciones (dropdown menu) -->
    <div class="absolute top-2 right-2 z-20">
      <!-- ... DropdownMenu ... -->
    </div>

    <!-- Card Header: Avatar y datos principales -->
    <CardHeader
      class="flex flex-row gap-4 items-start px-4 transition-transform duration-200 hover:scale-105 cursor-pointer"
      @click="user.roles?.[0]?.name === RoleEnum.ADMIN ? editUser() : showUser()"
    >
      <!-- Avatar -->
      <div class="flex-none w-20 flex flex-col items-center">
        <div class="w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border overflow-hidden">
          <Icon icon="mdi:image-outline" class="w-8 h-8 text-slate-400" />
        </div>
      </div>

      <!-- Info usuario -->
      <div class="flex-1 min-w-0">
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium',
            getRoleBadgeClass(user.roles?.[0]?.name)
          ]"
        >
          {{ getRoleLabelByString(user.roles?.[0]?.name) ?? 'Sin rol' }}
        </span>

        <div class="mt-2 flex items-center gap-2 min-w-0">
          <h3 class="text-sm font-semibold truncate">
            {{ user.name }} {{ user.surnames }}
          </h3>
          <Icon
            v-if="user.email_verified_at"
            icon="mdi:check-circle"
            class="w-4 h-4 text-emerald-500"
          />
        </div>

        <p class="mt-1 text-xs text-muted-foreground truncate">
          {{ user.email }}
        </p>
      </div>
    </CardHeader>

    <!-- Card Content: Habilidades (solo nannies) -->
    <CardContent v-if="user.roles?.[0]?.name === RoleEnum.NANNY && user.nanny?.qualities?.length">
      <ScrollArea class="w-full whitespace-nowrap">
        <div class="flex gap-2">
          <span
            v-for="(quality, idx) in user.nanny.qualities"
            :key="idx"
            class="flex-none text-xs px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800"
          >
            {{ quality.name }}
          </span>
        </div>
        <ScrollBar orientation="horizontal" class="pt-1 overflow-auto"/>
      </ScrollArea>
    </CardContent>

    <!-- Modal eliminar -->
    <DeleteModal
      v-model:show="showDeleteModal"
      :message="`¿Estás seguro de eliminar a ${user.name}?`"
      :onConfirm="confirmDeleteUser"
    />
  </Card>
</template>
```

---

## Patrones de Filtros

### FilterPanel Component Structure

Los componentes de filtro (como `UserFilters.vue`) deben:

1. **Recibir props:**
   - `show: boolean` - Controla visibilidad
   - `sortables: string[]` - Campos permitidos para filtrar

2. **Emitir eventos:**
   - `update:selectedFilters` - Objeto con valores de filtros
   - `update:filterFn` - Función de filtrado `(item: any) => boolean`
   - `closePopover` - Para cerrar el popover en móvil

3. **Implementar lógica de filtrado:**
   - Usar `ref()` para cada filtro individual
   - Usar `watch()` para emitir cambios
   - Proporcionar botón de reset

### Ejemplo: UserFilters.vue

```vue
<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { Checkbox } from "@/components/ui/checkbox"
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum'
import { Label } from '@/components/ui/label'
import Button from '@/components/ui/button/Button.vue'
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group"

defineProps({
  show: Boolean,
  sortables: Array as () => string[],
})

const emit = defineEmits(['update:selectedFilters', 'update:filterFn', 'closePopover'])

// Filtros locales
const roleFilter = ref<string[]>([])
const verifiedFilter = ref<string | null>(null)

// Toggle de roles (múltiple selección)
function toggleRole(role: string) {
  if (roleFilter.value.includes(role)) {
    roleFilter.value = roleFilter.value.filter(r => r !== role)
  } else {
    roleFilter.value.push(role)
  }
}

// Reset filters
function resetFilters() {
  roleFilter.value = []
  verifiedFilter.value = null
  nextTick(() => {
    // Forzar actualización si es necesario
  })
  emit('closePopover')
}

// Emitir filtros y función de filtrado
watch(
  [roleFilter, verifiedFilter],
  () => {
    const filters = {
      role: [...roleFilter.value],
      email_verified_at: verifiedFilter.value || '',
    }

    emit('update:selectedFilters', filters)

    emit('update:filterFn', (item: any) => {
      // Filtrar por roles
      if (roleFilter.value.length > 0) {
        const itemRoles = (item.roles || []).map((r: any) => r.name?.toLowerCase())
        const matchesRole = roleFilter.value.some(r => itemRoles.includes(r))
        if (!matchesRole) return false
      }

      // Filtrar por verificación
      if (verifiedFilter.value) {
        const isVerified = item.email_verified_at !== null
        if (verifiedFilter.value === 'verified' && !isVerified) return false
        if (verifiedFilter.value === 'unverified' && isVerified) return false
      }

      return true
    })
  },
  { immediate: true }
)
</script>

<template>
  <div v-if="show" class="p-4 space-y-3">
    <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-200">
      Filtrar por:
    </h4>

    <!-- Roles -->
    <Label>Rol</Label>
    <div v-if="sortables?.includes('role')" class="flex gap-4 flex-col">
      <div 
        v-for="roleKey in Object.values(RoleEnum)" 
        :key="roleKey" 
        class="flex items-center space-x-2"
      >
        <Checkbox 
          :checked="roleFilter.includes(roleKey)" 
          @click="toggleRole(roleKey)" 
          :id="`role-${roleKey}`" 
        />
        <label :for="`role-${roleKey}`">
          {{ getRoleLabelByString(roleKey) }}
        </label>
      </div>
    </div>

    <!-- Verificación -->
    <div>
      <Label>Verificación de correo</Label>
      <ToggleGroup 
        type="single"
        v-model="verifiedFilter"
        class="flex gap-2 mt-2"
      >
        <ToggleGroupItem value="verified" aria-label="Verificados">
          Verificados
        </ToggleGroupItem>
        <ToggleGroupItem value="unverified" aria-label="No verificados">
          No verificados
        </ToggleGroupItem>
      </ToggleGroup>
    </div>

    <!-- Botón reset -->
    <div class="pt-2 flex justify-end">
      <Button @click="resetFilters">
        <Icon icon="solar:restart-circle-linear" class="size-6"/>
        Limpiar filtros
      </Button>
    </div>
  </div>
</template>
```

---

## Buenas Prácticas

### 1. **Alias de Importación**

Siempre usa el alias `@/` para importar desde `resources/js/`:

```typescript
// ✅ Correcto
import DataTable from '@/components/datatable/DataTable.vue'
import type { User } from '@/types/User'

// ❌ Incorrecto (rutas relativas largas)
import DataTable from '../../../components/datatable/DataTable.vue'
```

### 2. **Type Safety**

Define interfaces TypeScript para todos los datos:

```typescript
// ✅ Correcto: Props tipados
const props = defineProps<{
  users: FetcherResponse<User>
  searchables: string[]
}>()

// ❌ Incorrecto: Sin tipos
const props = defineProps({
  users: Object,
  searchables: Array
})
```

### 3. **Case-Sensitivity**

Los nombres de archivos son **case-sensitive** en producción (Linux):

```typescript
// ✅ Correcto
import UserCard from './partials/UserCard.vue'

// ❌ Incorrecto (puede fallar en build)
import UserCard from './partials/usercard.vue'
```

### 4. **Claves en v-for**

Siempre usa claves únicas:

```vue
<!-- ✅ Correcto -->
<UserCard 
  v-for="user in users" 
  :key="user.ulid"
  :user="user" 
/>

<!-- ❌ Incorrecto -->
<UserCard 
  v-for="(user, index) in users" 
  :key="index"
  :user="user" 
/>
```

### 5. **Evitar Lógica Pesada en Slots**

Extrae lógica compleja a composables o servicios:

```vue
<!-- ✅ Correcto -->
<script setup>
import { useUserService } from '@/services/UserService'

const { getRoleBadgeClass } = useUserService(user)
</script>

<template>
  <span :class="getRoleBadgeClass(user.role)">
    {{ user.role }}
  </span>
</template>

<!-- ❌ Incorrecto: Lógica inline compleja -->
<template>
  <span :class="user.role === 'nanny' ? 'bg-pink-200 text-pink-500' : user.role === 'tutor' ? 'bg-sky-200 text-sky-500' : 'bg-slate-100'">
    {{ user.role }}
  </span>
</template>
```

### 6. **Mapeo de Campos Searchables**

Asegúrate de que los campos en `searchables` existan en tus datos:

```typescript
// Backend
$searchables = ['name', 'email', 'surnames']; // ✅ Nombres reales de columnas

// Frontend
<CardList :searchables="searchables" :items="users.data" />
```

### 7. **Whitelist de Sortables**

Define explícitamente qué campos son filtrables:

```php
// Backend: UserController
$sortables = ['role', 'email_verified_at']; // Solo estos campos permitidos
```

---

## Troubleshooting

### ❌ Error: "Cannot find module '@/components/datatable/DataTable.vue'"

**Causa:** El alias `@` no está configurado o la ruta es incorrecta.

**Solución:** 
1. Verifica `vite.config.ts`:
   ```typescript
   resolve: {
     alias: {
       '@': '/resources/js',
     }
   }
   ```
2. Verifica `tsconfig.json`:
   ```json
   {
     "compilerOptions": {
       "paths": {
         "@/*": ["./resources/js/*"]
       }
     }
   }
   ```

### ❌ Error: "Component name 'DataTable' should always be multi-word"

**Causa:** ESLint rule `vue/multi-word-component-names` activada.

**Solución:** Está desactivada en `eslint.config.js`:
```javascript
rules: {
  'vue/multi-word-component-names': 'off',
}
```

### ❌ Error: "Property 'data' does not exist on type 'FetcherResponse<User>'"

**Causa:** Tipo no importado o definido incorrectamente.

**Solución:**
```typescript
import type { FetcherResponse } from '@/types/FetcherResponse'
import type { User } from '@/types/User'

const props = defineProps<{
  users: FetcherResponse<User>
}>()

// Acceso correcto
const items = props.users.data
```

### ❌ CardList no muestra datos

**Checklist:**
1. ¿Estás pasando `users.data` en lugar de `users`?
   ```vue
   <!-- ✅ Correcto -->
   <CardList :items="users.data" />
   
   <!-- ❌ Incorrecto -->
   <CardList :items="users" />
   ```

2. ¿Los campos en `searchables` existen en los datos?
   ```typescript
   searchables: ['name', 'email'] // Deben ser claves reales
   ```

3. ¿El slot tiene la prop `item`?
   ```vue
   <template #default="{ item }">
     <UserCard :user="item" />
   </template>
   ```

### ❌ Filtros no funcionan

**Checklist:**
1. ¿El FilterPanel emite `update:filterFn`?
2. ¿La función retorna un boolean?
3. ¿Los campos en `sortables` coinciden con los filtros implementados?

### ❌ Build falla en producción pero funciona en dev

**Causa común:** Case-sensitivity de archivos en Linux.

**Solución:**
- Revisa todos los imports y asegúrate de que coincidan exactamente con los nombres de archivo
- Usa siempre PascalCase para componentes: `UserCard.vue`, no `userCard.vue`

---

## Extensiones Futuras

### Características Planificadas

#### 1. **Bulk Actions**
- Selección múltiple de items
- Acciones en lote (eliminar, exportar, etc.)
- Checkbox en cards/rows

#### 2. **Sorting Server-Side**
- Integrar ordenamiento con backend Fetcher
- Headers clickeables en DataTable
- Parámetros `?sort=name&direction=asc`

#### 3. **Export**
- Botón para exportar a CSV/Excel
- Export de datos filtrados
- Export de selección

#### 4. **Column Visibility Toggle**
- Mostrar/ocultar columnas en DataTable
- Persistir preferencias en localStorage
- Componente `SelectVisibleColumns`

#### 5. **Renderers Comunes**
- `DateRenderer` - Formatear fechas
- `CurrencyRenderer` - Formatear moneda
- `StatusBadgeRenderer` - Badges de estado
- `AvatarRenderer` - Mostrar avatares

#### 6. **Server-Side Pagination en CardList**
- Actualmente CardList usa paginación del cliente
- Migrar a paginación del servidor para grandes datasets
- Usar los links de `FetcherResponse`

### Guía para Implementar Sorting Server-Side

#### Backend
```php
// UserService.php
public function indexFetch(): LengthAwarePaginator
{
    $sortField = request('sort', 'created_at');
    $sortDirection = request('direction', 'desc');
    
    // Whitelist de campos permitidos
    $allowedSorts = ['name', 'email', 'created_at', 'email_verified_at'];
    
    if (!in_array($sortField, $allowedSorts)) {
        $sortField = 'created_at';
    }

    $users = User::query()->orderBy($sortField, $sortDirection);

    return Fetcher::for($users)
        ->paginate(10);
}
```

#### Frontend
```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3'

function handleSort(field: string) {
  router.get(route('users.index'), {
    sort: field,
    direction: currentDirection === 'asc' ? 'desc' : 'asc'
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>
```

---

## Mantenimiento de la Documentación

### Última actualización: 2025-10-24

### Log de Cambios

| Fecha | Cambio | Autor |
|-------|--------|-------|
| 2025-10-24 | Documentación inicial | Sweet Nanny Team |

### Para Mantener Actualizada esta Documentación

Al agregar/modificar componentes del DataTable:

1. **Actualizar la sección correspondiente** (Props, Slots, Eventos)
2. **Agregar ejemplos** si introduces nueva funcionalidad
3. **Actualizar el diagrama** si cambia el flujo de datos
4. **Registrar en Log de Cambios** con fecha y descripción
5. **Ejecutar tests** si implementas nuevas features
6. **Actualizar tipos** en `resources/js/types/` si es necesario

---

## Recursos Adicionales

- [TanStack Table Docs](https://tanstack.com/table/latest/docs/introduction)
- [Inertia.js Docs](https://inertiajs.com/)
- [Reka UI Components](https://reka-ui.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Laravel Pagination](https://laravel.com/docs/pagination)

---

## Contacto y Contribuciones

Para reportar bugs, sugerir mejoras o contribuir a estos componentes:

1. Abre un issue en el repositorio
2. Sigue las convenciones de código del proyecto (ver `.github/copilot-instructions.md`)
3. Ejecuta linters antes de hacer commit:
   ```bash
   npm run lint
   npm run format
   composer pint
   ```

---

**¿Preguntas?** Consulta el código de ejemplo en `resources/js/Pages/User/` o revisa esta documentación.
