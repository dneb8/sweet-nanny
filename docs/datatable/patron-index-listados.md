# Patrón de Implementación para Listados Index con DataTable

## 1. Introducción y Objetivo

Este documento describe el patrón arquitectónico completo para implementar listados tipo "index" (Usuarios, Nannies, Tutores, Bookings, etc.) utilizando el componente DataTable reutilizable.

**Objetivo Principal:** Proporcionar una guía completa y reproducible que permita crear nuevos listados paginados, filtrables y ordenables **sin modificar** los componentes base del DataTable.

**Componentes Reutilizables (NO MODIFICAR):**
- `resources/js/components/datatable/*` - Todos los archivos en esta carpeta son componentes base genéricos.

**Componentes Específicos por Módulo (CREAR/MODIFICAR):**
- Backend: `Controller`, `Service`, `Builder`, `Scopes`
- Frontend: `Pages/*/Index.vue`, `*Table.vue`, `*Filtros.vue`, `*TableService.ts`

---

## 2. Diagrama del Flujo End-to-End

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           FLUJO COMPLETO DEL DATATABLE                       │
└─────────────────────────────────────────────────────────────────────────────┘

                                BACKEND
┌────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│  1. HTTP Request                                                            │
│     GET /users?searchTerm=juan&filters[role]=tutor&sortBy=name&page=2      │
│                            ↓                                                │
│  2. Routes (routes/web.php)                                                 │
│     Route::get('/users', [UserController::class, 'index'])                 │
│                            ↓                                                │
│  3. Controller (app/Http/Controllers/UserController.php)                   │
│     public function index(UserService $userService): Response              │
│     {                                                                       │
│         $users = $userService->indexFetch();                               │
│         return Inertia::render('User/Index', [                             │
│             'users' => $users,                                             │
│             'roles' => $roles,                                             │
│         ]);                                                                 │
│     }                                                                       │
│                            ↓                                                │
│  4. Service (app/Services/UserService.php)                                 │
│     public function indexFetch(): LengthAwarePaginator                     │
│     {                                                                       │
│         $users = User::query()->with(['roles', 'nanny', 'tutor']);         │
│                                                                             │
│         return Fetcher::for($users)                                        │
│             ->allowFilters([...])                                          │
│             ->allowSort([...])                                             │
│             ->allowSearch([...])                                           │
│             ->paginate(12);                                                │
│     }                                                                       │
│                            ↓                                                │
│  5. Fetcher (app/Classes/Fetcher/Fetcher.php)                             │
│     - Aplica búsqueda en campos permitidos                                 │
│     - Aplica filtros mediante scopes                                       │
│     - Aplica ordenamiento en campos permitidos                             │
│     - Pagina resultados                                                    │
│                            ↓                                                │
│  6. Builder/Scopes (app/Eloquent/Builders/UserBuilder.php)                │
│                    (app/Scopes/User/FiltrarPorRole.php)                   │
│     - Aplican lógica específica de filtrado                                │
│     - Modifican el query según parámetros                                  │
│                            ↓                                                │
│  7. Model (app/Models/User.php)                                            │
│     - Define relaciones (roles, nanny, tutor)                              │
│     - Ejecuta query final                                                  │
│                            ↓                                                │
│  8. Response (FetcherResponse<User>)                                       │
│     {                                                                       │
│         "data": [...],           // Array de registros                      │
│         "current_page": 2,       // Página actual                          │
│         "per_page": 12,          // Registros por página                   │
│         "total": 145,            // Total de registros                     │
│         "last_page": 13,         // Última página                          │
│         "from": 13,              // Primer registro (índice)               │
│         "to": 24,                // Último registro (índice)               │
│         "links": [...],          // Enlaces de paginación                  │
│         ...                                                                 │
│     }                                                                       │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

                               FRONTEND
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│  9. Inertia Response → Vue Component                                        │
│                            ↓                                                │
│  10. Pages/User/Index.vue                                                   │
│      - Recibe props: { users: FetcherResponse<User>, roles: string[] }     │
│      - Renderiza estructura básica de la página                            │
│      - Pasa resource a UserTable                                           │
│                            ↓                                                │
│  11. Pages/User/components/UserTable.vue                                    │
│      <DataTable                                                             │
│        :resource="users"                                                    │
│        resourcePropName="users"                                            │
│        use-filters                                                         │
│        :canToggleColumnsVisibility="true"                                  │
│        v-model:visibleColumns="visibleColumns"                             │
│        :responsiveCards="'lg'"                                             │
│      >                                                                      │
│        <template #filters>                                                 │
│          <UserFiltros v-model:filtros="filtros" :roles />                 │
│        </template>                                                         │
│                                                                             │
│        <template #responsive-card="{ slotProps }">                         │
│          <UserCard :user="slotProps" />                                    │
│        </template>                                                         │
│                                                                             │
│        <Column header="Nombre" :sortable="true">                           │
│          <template #body="slotProps">                                      │
│            {{ slotProps.record?.name ?? "—" }}                             │
│          </template>                                                        │
│        </Column>                                                            │
│        ...                                                                  │
│      </DataTable>                                                           │
│                            ↓                                                │
│  12. components/datatable/Main.vue (COMPONENTE BASE)                       │
│      - Renderiza tabla o cards según breakpoint                            │
│      - Gestiona paginación, búsqueda, filtros                              │
│      - Emite eventos para refetch (router.get con Inertia)                 │
│                            ↓                                                │
│  13. User Interaction (buscar, filtrar, ordenar, paginar)                  │
│      - DataTable actualiza URL query params                                │
│      - router.get() hace request a backend preservando estado              │
│      - Ciclo se repite desde paso 1                                        │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Contrato del Backend

### 3.1. Query Parameters Soportados

El DataTable espera y envía los siguientes parámetros en el querystring:

| Parámetro | Tipo | Descripción | Ejemplo | Origen |
|-----------|------|-------------|---------|--------|
| `searchTerm` | `string` | Término de búsqueda global | `?searchTerm=juan` | Barra de búsqueda |
| `sortBy` | `string` | Campo para ordenar (whitelist) | `?sortBy=name` | Click en header columna |
| `sortDirection` | `asc\|desc` | Dirección de ordenamiento | `?sortDirection=asc` | Click en header columna |
| `page` | `integer` | Número de página actual | `?page=2` | Paginación |
| `per_page` | `integer\|"all"` | Registros por página | `?per_page=25` | Selector de items |
| `filters[campo]` | `mixed` | Filtros específicos | `?filters[role]=tutor` | Panel de filtros |

### 3.2. Ejemplo de URL Completa

```
/users?searchTerm=maria&filters[role]=nanny&sortBy=email&sortDirection=desc&page=3&per_page=12
```

### 3.3. Estructura de Respuesta (FetcherResponse)

El Service debe retornar un `LengthAwarePaginator` de Laravel, que automáticamente incluye:

```typescript
interface FetcherResponse<T> {
    current_page: number;        // Página actual (ej: 2)
    data: Array<T>;              // Array de registros (ej: User[])
    first_page_url: string;      // URL primera página
    from: number;                // Índice del primer registro mostrado (ej: 13)
    last_page: number;           // Número de última página (ej: 12)
    last_page_url: string;       // URL última página
    links: Array<{               // Links de paginación
        active: boolean;
        label: string;
        url: string | null;
    }>;
    next_page_url: string | null;
    path: string;                // Ruta base (ej: "/users")
    per_page: number;            // Items por página (ej: 12)
    prev_page_url: string | null;
    to: number;                  // Índice del último registro mostrado (ej: 24)
    total: number;               // Total de registros (ej: 145)
}
```

**Campos Obligatorios para el DataTable:**
- `data` - Array de registros
- `current_page` - Página actual
- `per_page` - Items por página
- `total` - Total de registros

### 3.4. Implementación del Service

**Patrón estándar usando Fetcher:**

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
        // 1. Query base con relaciones
        $users = User::query()
            ->with([
                'roles',
                'nanny',
                'nanny.qualities',
                'tutor',
            ])
            ->orderBy('created_at', 'desc');

        // 2. Definir campos ordenables (whitelist)
        $sortables = ['email', 'name', 'surnames'];
        
        // 3. Definir campos buscables
        $searchables = ['email', 'name', 'surnames'];

        // 4. Usar Fetcher para aplicar filtros, búsqueda, ordenamiento y paginación
        $users = Fetcher::for($users)
            ->allowFilters([
                'role' => [
                    'using' => fn (Filter $filter) => $filter->usingScope('filtrarPorRole'),
                ],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(12); // Default per_page

        return $users;
    }
}
```

### 3.5. Builder y Scopes

**Builder (app/Eloquent/Builders/UserBuilder.php):**

```php
<?php

namespace App\Eloquent\Builders;

use App\Scopes\User\FiltrarPorRole;
use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    /**
     * Filtra por rol de usuario.
     */
    public function filtrarPorRole($role)
    {
        return $this->tap(new FiltrarPorRole($role));
    }
}
```

**Scope (app/Scopes/User/FiltrarPorRole.php):**

```php
<?php

namespace App\Scopes\User;

use Illuminate\Database\Eloquent\Builder;

final readonly class FiltrarPorRole
{
    public function __construct(
        public string|array|null $role = null,
    ) {}

    public function __invoke(Builder $query): Builder
    {
        if (empty($this->role)) {
            return $query;
        }

        $roles = is_array($this->role) ? $this->role : [$this->role];

        return $query->whereHas('roles', function (Builder $q) use ($roles) {
            $q->whereIn('name', $roles);
        });
    }
}
```

**Registrar Builder en el Model:**

```php
<?php

namespace App\Models;

use App\Eloquent\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}
```

### 3.6. Controller

**Patrón estándar:**

```php
<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Inertia\{Inertia, Response};

class UserController extends Controller
{
    public function index(UserService $userService): Response
    {
        // 1. Obtener datos paginados del service
        $users = $userService->indexFetch();
        
        // 2. Preparar datos adicionales (enums, opciones, etc.)
        $roles = array_map(fn($role) => $role->value, RoleEnum::cases());

        // 3. Renderizar con Inertia
        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => array_values($roles),
        ]);
    }
}
```

---

## 4. Contrato del Frontend (DataTable)

### 4.1. Props del DataTable

| Prop | Tipo | Requerido | Default | Descripción |
|------|------|-----------|---------|-------------|
| `resource` | `Object` | ✅ Sí | - | FetcherResponse<T> del backend |
| `resourcePropName` | `String` | ✅ Sí | - | Nombre de la prop en el servidor (para Inertia `only`) |
| `useSearch` | `Boolean` | ❌ No | `true` | Mostrar barra de búsqueda |
| `useFilters` | `Boolean` | ❌ No | `false` | Mostrar botón de filtros |
| `usePagination` | `Boolean` | ❌ No | `true` | Mostrar paginación |
| `canToggleColumnsVisibility` | `Boolean` | ❌ No | `false` | Permitir ocultar/mostrar columnas |
| `visibleColumns` | `Array<string>` | ❌ No | `[]` | Array de nombres de columnas visibles (v-model) |
| `responsiveCards` | `String` | ❌ No | `""` | Breakpoint para cards: 'sm', 'md', 'lg', 'xl' |
| `selectableRows` | `Boolean` | ❌ No | `false` | Permitir selección de filas |
| `keyRowsBy` | `String` | ❌ No | `"id"` | Campo único para key en v-for |

### 4.2. Props de Column

| Prop | Tipo | Requerido | Default | Descripción |
|------|------|-----------|---------|-------------|
| `header` | `String` | ✅ Sí | - | Texto del encabezado |
| `field` | `String` | ❌ No | - | Campo del modelo (para ordenamiento) |
| `sortable` | `Boolean` | ❌ No | `false` | Columna ordenable |
| `togglable` | `Boolean` | ❌ No | `false` | Puede ocultarse/mostrarse |
| `collapseAlways` | `Boolean` | ❌ No | `false` | Siempre oculta (acciones móviles) |
| `headerClass` | `String` | ❌ No | `""` | Clases CSS para <th> |

### 4.3. Slots Disponibles

#### Slot: `#filters`
Panel de filtros personalizado. Se renderiza dentro de un Popover.

```vue
<template #filters>
    <UserFiltros v-model:filtros="filtros" :roles="roles" />
</template>
```

#### Slot: `#responsive-card`
Card personalizado para vista móvil. Recibe `slotProps` con el registro completo.

```vue
<template #responsive-card="{ slotProps }">
    <UserCard :user="slotProps" />
</template>
```

#### Slot: `Column #body`
Contenido personalizado de una celda. Recibe `slotProps.record` con el registro completo.

```vue
<Column header="Nombre" :sortable="true">
    <template #body="slotProps">
        {{ slotProps.record?.name ?? "—" }}
    </template>
</Column>
```

#### Slot: `#searchbar`
Reemplaza la barra de búsqueda por defecto.

#### Slot: `#before-actions`, `#after-actions`
Insertar elementos antes/después de los controles (filtros, columnas).

#### Slot: `#before-table`, `#after-table`
Insertar contenido antes/después de la tabla.

### 4.4. Eventos Emitidos

| Evento | Payload | Descripción |
|--------|---------|-------------|
| `update:visibleColumns` | `Array<string>` | Cambio en columnas visibles |
| `update:selectedRows` | `Array<any>` | Cambio en filas seleccionadas |
| `sort-change` | `{ column, direction }` | Cambio de ordenamiento (interno) |
| `page-change` | `{ page }` | Cambio de página (interno) |
| `per-page-change` | `{ offset }` | Cambio de items por página (interno) |

**Nota:** Los eventos son manejados internamente por el DataTable, que ejecuta `router.get()` con Inertia para refetch.

---

## 5. Slots y Renders Personalizados

### 5.1. Renderizado de Columnas

**Opción 1: Mostrar campo directo**
```vue
<Column header="Email" field="email" :sortable="true">
    <template #body="slotProps">
        {{ slotProps.record.email }}
    </template>
</Column>
```

**Opción 2: Con transformación**
```vue
<Column header="Fecha Creación" field="created_at" :sortable="true">
    <template #body="slotProps">
        {{ new Date(slotProps.record.created_at).toLocaleDateString() }}
    </template>
</Column>
```

**Opción 3: Con componentes**
```vue
<Column header="Rol">
    <template #body="slotProps">
        <Badge 
            :label="getRoleLabelByString(slotProps.record?.roles?.[0]?.name ?? '') || 'Sin rol'"
            :customClass="getRoleBadgeClasses(slotProps.record?.roles?.[0]?.name ?? '')"
        />
    </template>
</Column>
```

**Opción 4: Columna de acciones**
```vue
<Column header="Acciones" field="id">
    <template #body="slotProps">
        <div class="grid grid-cols-3 gap-2">
            <div @click="editarUsuario(slotProps.record)" 
                 class="flex justify-center items-center text-blue-600 hover:cursor-pointer"
                 title="Editar">
                <Icon icon="mdi:edit-outline" :size="20" />
            </div>
            
            <div @click="abrirModalEliminarUsuario(slotProps.record)"
                 class="flex justify-center items-center text-rose-600 hover:cursor-pointer"
                 title="Eliminar">
                <Icon icon="fluent:delete-12-regular" :size="20" />
            </div>
        </div>
    </template>
</Column>
```

### 5.2. Responsive Cards

Para activar cards responsivos, usa la prop `responsiveCards`:

```vue
<DataTable 
    :resource="users"
    :responsiveCards="'lg'"  <!-- Cambia a cards en pantallas < lg (976px) -->
>
    <template #responsive-card="{ slotProps }">
        <UserCard :user="slotProps" />
    </template>
</DataTable>
```

**Breakpoints disponibles:**
- `'sm'` - 480px
- `'md'` - 768px
- `'lg'` - 976px (recomendado)
- `'xl'` - 1440px

**Ejemplo de Card Component:**

```vue
<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card';
import type { User } from '@/types/User';

const props = defineProps<{
    user: User;
}>();
</script>

<template>
    <Card class="relative overflow-hidden">
        <CardHeader class="flex flex-row gap-4 items-start">
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold truncate">
                    {{ props.user.name }} {{ props.user.surnames }}
                </h3>
                <p class="mt-1 text-xs text-muted-foreground truncate">
                    {{ props.user.email }}
                </p>
            </div>
        </CardHeader>
    </Card>
</template>
```

### 5.3. Filtros Personalizados

**Componente de Filtros (UserFiltros.vue):**

```vue
<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface FiltrosUser {
    role: string | null;
}

defineProps<{
    roles: Array<string>;
}>();

const filtros = defineModel<FiltrosUser>('filtros', { 
    default: { role: null }
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 w-full">
        <div class="w-full">
            <label for="filtro-role" class="mb-1 ml-1">Rol</label>
            <Select v-model="filtros.role">
                <SelectTrigger>
                    <SelectValue placeholder="Selecciona un rol" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem v-for="role in roles" :key="role" :value="role">
                            {{ getRoleLabelByString(role) }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>
```

**Integración en el Table:**

```vue
<DataTable :resource="users" use-filters>
    <template #filters>
        <UserFiltros v-model:filtros="filtros" :roles="roles" />
    </template>
</DataTable>
```

El DataTable automáticamente:
1. Detecta cambios en `filtros` (vía `provide/inject`)
2. Construye query params como `filters[role]=tutor`
3. Ejecuta `router.get()` con Inertia preservando estado

---

## 6. Acciones por Fila (Table Service)

### 6.1. Patrón de Service

Cada módulo debe tener su propio `*TableService.ts` que expone:
- Estado reactivo (modales, registros seleccionados)
- Handlers para acciones (ver, editar, eliminar)
- Lógica de presentación (clases de badges, formateos)

**Ejemplo: userTableService.ts**

```typescript
import { computed, provide, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { User } from "@/types/User";
import { RoleEnum } from "@/enums/role.enum";
import { FiltrosUser } from "@/Pages/User/components/UserFiltros.vue";

export class UserTableService {
    // Estado reactivo
    public usuarioEliminar = ref<User | null>(null);
    public modalEliminarUsuario = ref(false);
    
    // Filtros reactivos
    public filtros = ref<FiltrosUser>({
        role: null,
    });
    
    // Columnas visibles
    public visibleColumns = ref<Array<string>>([
        'Perfil',
        'Nombre',
        'Apellidos',
        'Correo Electrónico',
        'Acciones', 
        'Rol',
    ]);

    public constructor() {
        // Providers para comunicación con DataTable/Filters
        provide("users_filters", computed(() => this.getFilters()));
        provide("clear_users_filters", () => {
            this.filtros.value = { role: null };
        });
    }

    // Getter de filtros
    public getFilters = () => ({
        role: this.filtros.value.role,
    });

    // Acciones
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

    // Helpers de presentación
    public getRoleBadgeClasses = (role: RoleEnum): string => {
        const classes: Record<RoleEnum, string> = {
            [RoleEnum.ADMIN]: 'bg-emerald-200/70 text-emerald-500 dark:bg-emerald-400/25',
            [RoleEnum.NANNY]: 'bg-pink-200/70 text-pink-500 dark:bg-pink-400/25',
            [RoleEnum.TUTOR]: 'bg-sky-200/70 text-sky-500 dark:bg-sky-400/25',
        };
        return classes[role] ?? '';
    }
}
```

### 6.2. Uso en el Componente de Tabla

```vue
<script setup lang="ts">
import DataTable from "@/components/datatable/Main.vue";
import Column from "@/components/datatable/Column.vue";
import type { FetcherResponse } from "@/types/FetcherResponse";
import type { User } from "@/types/User";
import { UserTableService } from "@/services/userTableService";

defineProps<{
    resource: FetcherResponse<User>;
    roles: Array<string>;
}>();

// Instanciar service
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
    >
        <!-- Usar handlers del service -->
        <Column header="Acciones" field="id">
            <template #body="slotProps">
                <div @click="editarUsuario(slotProps.record)">
                    <Icon icon="mdi:edit-outline" />
                </div>
                <div @click="abrirModalEliminarUsuario(slotProps.record)">
                    <Icon icon="fluent:delete-12-regular" />
                </div>
            </template>
        </Column>
    </DataTable>
    
    <!-- Modal de confirmación -->
    <DeleteModal
        v-model:show="modalEliminarUsuario"
        :message="`¿Eliminar a ${usuarioEliminar?.name}?`"
        :onConfirm="eliminarUsuario"
    />
</template>
```

---

## 7. Convenciones Importantes

### 7.1. Mapeo de Column.field ↔ Ordenamiento Backend

**IMPORTANTE:** El atributo `field` de `<Column>` debe coincidir con los campos definidos en `allowSort()` del Fetcher.

**❌ Incorrecto:**
```vue
<!-- Frontend -->
<Column header="Nombre" field="full_name" :sortable="true" />
```
```php
// Backend - NO coincide!
$sortables = ['name', 'email'];
```

**✅ Correcto:**
```vue
<!-- Frontend -->
<Column header="Nombre" field="name" :sortable="true" />
```
```php
// Backend
$sortables = ['name', 'email', 'surnames'];
```

**Cuando el frontend envía:**
```
?sortBy=name&sortDirection=asc
```

**El backend debe:**
1. Validar que `name` esté en el array `$sortables`
2. Aplicar `orderBy('name', 'asc')`

### 7.2. Nomenclatura de Props

Consistencia en nombres de props de Inertia:

```php
// Controller
return Inertia::render('User/Index', [
    'users' => $users,  // ← Nombre en plural
    'roles' => $roles,
]);
```

```vue
<!-- Page -->
<script setup lang="ts">
defineProps<{
    users: FetcherResponse<User>;  // ← Mismo nombre
    roles: Array<string>;
}>();
</script>

<template>
    <UserTable 
        :resource="users" 
        resourcePropName="users"  // ← Mismo nombre para Inertia 'only'
    />
</template>
```

### 7.3. Estructura de Carpetas

```
app/
├── Http/
│   └── Controllers/
│       └── UserController.php
├── Services/
│   └── UserService.php
├── Eloquent/
│   └── Builders/
│       └── UserBuilder.php
├── Scopes/
│   └── User/
│       └── FiltrarPorRole.php
└── Models/
    └── User.php

resources/js/
├── Pages/
│   └── User/
│       ├── Index.vue
│       └── components/
│           ├── UserTable.vue
│           ├── UserFiltros.vue
│           └── UserCard.vue
├── services/
│   └── userTableService.ts
└── types/
    ├── User.d.ts
    └── FetcherResponse.d.ts
```

### 7.4. Persistencia de Columnas Visibles (Opcional)

Si deseas guardar preferencias del usuario:

```typescript
import { useLocalStorage } from '@vueuse/core';

export class UserTableService {
    public visibleColumns = useLocalStorage<Array<string>>(
        'user-table-visible-columns',
        ['Perfil', 'Nombre', 'Email', 'Acciones']
    );
}
```

---

## 8. Ejemplo Práctico: "Nannies Index"

### 8.1. Backend

**Ruta (routes/web.php):**
```php
Route::get('/nannies', [NannyController::class, 'index'])->name('nannies.index');
```

**Controller (app/Http/Controllers/NannyController.php):**
```php
<?php

namespace App\Http\Controllers;

use App\Services\NannyService;
use Inertia\{Inertia, Response};

class NannyController extends Controller
{
    public function index(NannyService $nannyService): Response
    {
        $nannies = $nannyService->indexFetch();
        
        $disponibilidades = ['mañana', 'tarde', 'noche'];

        return Inertia::render('Nanny/Index', [
            'nannies' => $nannies,
            'disponibilidades' => $disponibilidades,
        ]);
    }
}
```

**Service (app/Services/NannyService.php):**
```php
<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Models\Nanny;
use Illuminate\Pagination\LengthAwarePaginator;

class NannyService
{
    public function indexFetch(): LengthAwarePaginator
    {
        $nannies = Nanny::query()
            ->with(['user', 'qualities', 'experiences'])
            ->orderBy('created_at', 'desc');

        $sortables = ['user.name', 'experiencia', 'tarifa'];
        $searchables = ['user.name', 'user.surnames', 'user.email'];

        return Fetcher::for($nannies)
            ->allowFilters([
                'disponibilidad' => [
                    'using' => fn (Filter $filter) => $filter->usingScope('filtrarPorDisponibilidad'),
                ],
                'experiencia_min' => [
                    'as' => 'experiencia',
                    'using' => fn (Filter $filter) => $filter->transform(fn($val) => (int)$val),
                ],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(15);
    }
}
```

**Builder (app/Eloquent/Builders/NannyBuilder.php):**
```php
<?php

namespace App\Eloquent\Builders;

use App\Scopes\Nanny\FiltrarPorDisponibilidad;
use Illuminate\Database\Eloquent\Builder;

class NannyBuilder extends Builder
{
    public function filtrarPorDisponibilidad($disponibilidad)
    {
        return $this->tap(new FiltrarPorDisponibilidad($disponibilidad));
    }
}
```

**Scope (app/Scopes/Nanny/FiltrarPorDisponibilidad.php):**
```php
<?php

namespace App\Scopes\Nanny;

use Illuminate\Database\Eloquent\Builder;

final readonly class FiltrarPorDisponibilidad
{
    public function __construct(
        public string|array|null $disponibilidad = null,
    ) {}

    public function __invoke(Builder $query): Builder
    {
        if (empty($this->disponibilidad)) {
            return $query;
        }

        $disponibilidades = is_array($this->disponibilidad) 
            ? $this->disponibilidad 
            : [$this->disponibilidad];

        return $query->whereHas('disponibilidades', function (Builder $q) use ($disponibilidades) {
            $q->whereIn('tipo', $disponibilidades);
        });
    }
}
```

**Model (app/Models/Nanny.php):**
```php
<?php

namespace App\Models;

use App\Eloquent\Builders\NannyBuilder;
use Illuminate\Database\Eloquent\Model;

class Nanny extends Model
{
    public function user() { return $this->belongsTo(User::class); }
    public function qualities() { return $this->hasMany(Quality::class); }
    public function disponibilidades() { return $this->hasMany(Disponibilidad::class); }

    public function newEloquentBuilder($query): NannyBuilder
    {
        return new NannyBuilder($query);
    }
}
```

### 8.2. Frontend

**Page (resources/js/Pages/Nanny/Index.vue):**
```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Nanny } from '@/types/Nanny';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import NannyTable from './components/NannyTable.vue';

defineProps<{
    nannies: FetcherResponse<Nanny>;
    disponibilidades: Array<string>;
}>();
</script>

<template>
    <Head title="Nannies" />

    <div class="flex flex-row justify-between mb-4">
        <Heading icon="mdi:account-group" title="Listado de Nannies" />
        <Link :href="route('nannies.create')">
            <Button>Crear Nanny</Button>
        </Link>
    </div>

    <NannyTable :resource="nannies" :disponibilidades />
</template>
```

**Table (resources/js/Pages/Nanny/components/NannyTable.vue):**
```vue
<script setup lang="ts">
import DataTable from "@/components/datatable/Main.vue";
import Column from "@/components/datatable/Column.vue";
import type { FetcherResponse } from "@/types/FetcherResponse";
import type { Nanny } from "@/types/Nanny";
import NannyCard from "./NannyCard.vue";
import NannyFiltros from "./NannyFiltros.vue";
import { NannyTableService } from "@/services/nannyTableService";

defineProps<{
    resource: FetcherResponse<Nanny>;
    disponibilidades: Array<string>;
}>();

const {
    filtros,
    visibleColumns,
    editarNanny,
    eliminarNanny,
} = new NannyTableService();
</script>

<template>
    <DataTable
        :resource="resource"
        resourcePropName="nannies"
        use-filters
        :canToggleColumnsVisibility="true"
        v-model:visibleColumns="visibleColumns"
        :responsiveCards="'lg'"
    >
        <template #filters>
            <NannyFiltros v-model:filtros="filtros" :disponibilidades />
        </template>

        <template #responsive-card="{ slotProps }">
            <NannyCard :nanny="slotProps" />
        </template>

        <Column header="Nombre" field="user.name" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.user?.name ?? "—" }}
            </template>
        </Column>

        <Column header="Experiencia (años)" field="experiencia" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.experiencia ?? 0 }}
            </template>
        </Column>

        <Column header="Tarifa" field="tarifa" :sortable="true">
            <template #body="slotProps">
                ${{ slotProps.record?.tarifa ?? 0 }}
            </template>
        </Column>

        <Column header="Acciones" field="id">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <Icon icon="mdi:edit-outline" @click="editarNanny(slotProps.record)" />
                    <Icon icon="mdi:delete-outline" @click="eliminarNanny(slotProps.record)" />
                </div>
            </template>
        </Column>
    </DataTable>
</template>
```

**Filtros (resources/js/Pages/Nanny/components/NannyFiltros.vue):**
```vue
<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface FiltrosNanny {
    disponibilidad: string | null;
    experiencia_min: number | null;
}

defineProps<{
    disponibilidades: Array<string>;
}>();

const filtros = defineModel<FiltrosNanny>('filtros', { 
    default: {
        disponibilidad: null,
        experiencia_min: null,
    }
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
        <div>
            <label class="mb-1 ml-1">Disponibilidad</label>
            <Select v-model="filtros.disponibilidad">
                <SelectTrigger>
                    <SelectValue placeholder="Selecciona" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem v-for="disp in disponibilidades" :key="disp" :value="disp">
                            {{ disp }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div>
            <label class="mb-1 ml-1">Experiencia mínima (años)</label>
            <Input v-model.number="filtros.experiencia_min" type="number" min="0" />
        </div>
    </div>
</template>
```

**Service (resources/js/services/nannyTableService.ts):**
```typescript
import { computed, provide, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Nanny } from "@/types/Nanny";
import { FiltrosNanny } from "@/Pages/Nanny/components/NannyFiltros.vue";

export class NannyTableService {
    public filtros = ref<FiltrosNanny>({
        disponibilidad: null,
        experiencia_min: null,
    });

    public visibleColumns = ref<Array<string>>([
        'Nombre',
        'Experiencia (años)',
        'Tarifa',
        'Acciones',
    ]);

    public constructor() {
        provide("nannies_filters", computed(() => this.getFilters()));
        provide("clear_nannies_filters", () => {
            this.filtros.value = {
                disponibilidad: null,
                experiencia_min: null,
            };
        });
    }

    public getFilters = () => ({
        disponibilidad: this.filtros.value.disponibilidad,
        experiencia_min: this.filtros.value.experiencia_min,
    });

    public editarNanny = (nanny: Nanny) => {
        router.get(route('nannies.edit', nanny.id));
    };

    public eliminarNanny = (nanny: Nanny) => {
        if (confirm(`¿Eliminar a ${nanny.user?.name}?`)) {
            router.delete(route('nannies.destroy', nanny.id));
        }
    };
}
```

---

## 9. Troubleshooting / FAQ

### 9.1. Los filtros no se aplican

**Problema:** Cambio los filtros pero no se refrescan los datos.

**Solución:**
1. Verifica que `use-filters` esté en `true` en `<DataTable>`
2. Asegúrate de usar `v-model:filtros` en el componente de filtros
3. Revisa que el `provide/inject` esté configurado correctamente en el service:
   ```typescript
   provide("users_filters", computed(() => this.getFilters()));
   ```
4. Verifica en DevTools Network que se envíen los query params `filters[campo]=valor`

### 9.2. El ordenamiento no funciona

**Problema:** Click en header de columna no ordena.

**Solución:**
1. Verifica que la columna tenga `:sortable="true"`
2. Asegúrate de que `field` coincida con el campo en `allowSort()` del backend
3. Revisa que el campo esté en el array `$sortables` del Service
4. Verifica en DevTools Network que se envíen `sortBy` y `sortDirection`

### 9.3. Paginación no funciona

**Problema:** Cambio de página pero no se actualizan datos.

**Solución:**
1. Verifica que `usePagination` esté en `true` (es el default)
2. Asegúrate de que el backend retorne un `LengthAwarePaginator`
3. Verifica que `resourcePropName` coincida con el nombre de la prop en el controller
4. Revisa en DevTools Network que se envíe el query param `page=X`

### 9.4. Cards responsivos no aparecen

**Problema:** No se muestran cards en móvil.

**Solución:**
1. Asegúrate de tener el slot `#responsive-card` definido
2. Verifica que `responsiveCards` tenga un valor ('sm', 'md', 'lg', 'xl')
3. Prueba redimensionando la ventana o usando DevTools mobile emulation

### 9.5. Error: "Cannot read property 'data' of undefined"

**Problema:** El DataTable no recibe el resource correctamente.

**Solución:**
1. Verifica que el controller retorne el paginator con el nombre correcto:
   ```php
   'users' => $users,  // ← Debe coincidir con resourcePropName
   ```
2. Asegúrate de pasar `:resource="users"` al DataTable
3. Verifica que el Service retorne un `LengthAwarePaginator`, no un `Collection`

### 9.6. Búsqueda no encuentra resultados

**Problema:** Escribo en searchbar pero no encuentra nada.

**Solución:**
1. Verifica que los campos en `allowSearch()` existan en el modelo
2. Asegúrate de que `useSearch` esté en `true` (es el default)
3. Revisa que el término de búsqueda se envíe como `searchTerm` en el query
4. Para búsquedas en relaciones, usa dot notation: `'user.name'`

### 9.7. Columnas visibles no persisten

**Problema:** Cambio columnas visibles pero se resetean al recargar.

**Solución:**
1. Usa `useLocalStorage` de `@vueuse/core`:
   ```typescript
   public visibleColumns = useLocalStorage('user-visible-cols', []);
   ```
2. Alternativamente, guarda en backend con un endpoint de preferencias

### 9.8. Performance lento con muchos registros

**Problema:** La tabla tarda mucho en cargar.

**Solución:**
1. Agrega índices en base de datos para campos ordenables/filtrados
2. Usa `eager loading` con `with()` para evitar N+1 queries
3. Reduce el `per_page` default (ej: 10 o 15 en vez de 50)
4. Considera paginación cursor-based para datasets muy grandes

### 9.9. Error: "Cannot match against expected injection 'XXX_filters'"

**Problema:** El DataTable no encuentra los filtros.

**Solución:**
1. Verifica que en el Service uses:
   ```typescript
   provide("users_filters", computed(() => this.getFilters()));
   ```
2. El nombre debe ser `{resourcePropName}_filters` (ej: `users_filters`)
3. Asegúrate de instanciar el Service en el componente de tabla, no en Index

### 9.10. Case Sensitivity en Imports

**Problema:** Error de compilación "Cannot find module '@/Pages/user/Index.vue'".

**Solución:**
1. Usa siempre PascalCase para carpetas de módulos: `Pages/User/`, no `Pages/user/`
2. Mantén consistencia en imports:
   ```typescript
   import UserTable from '@/Pages/User/components/UserTable.vue';
   ```

---

## 10. Checklist Rápida para Nuevos Listados

Ver documento separado: `docs/datatable/checklist-nuevo-listado.md`

---

## 11. Referencias

- **Backend:**
  - Fetcher: `app/Classes/Fetcher/Fetcher.php`
  - Filter: `app/Classes/Fetcher/Filter.php`
  - Ejemplo Controller: `app/Http/Controllers/UserController.php`
  - Ejemplo Service: `app/Services/UserService.php`
  - Ejemplo Builder: `app/Eloquent/Builders/UserBuilder.php`
  - Ejemplo Scope: `app/Scopes/User/FiltrarPorRole.php`

- **Frontend:**
  - DataTable Base: `resources/js/components/datatable/Main.vue`
  - Column: `resources/js/components/datatable/Column.vue`
  - Ejemplo Page: `resources/js/Pages/User/Index.vue`
  - Ejemplo Table: `resources/js/Pages/User/components/UserTable.vue`
  - Ejemplo Filtros: `resources/js/Pages/User/components/UserFiltros.vue`
  - Ejemplo Service: `resources/js/services/userTableService.ts`

- **Tipos:**
  - FetcherResponse: `resources/js/types/FetcherResponse.d.ts`

- **Documentación Adicional:**
  - README del DataTable: `resources/js/components/datatable/README.md`
  - Checklist: `docs/datatable/checklist-nuevo-listado.md`

---

## Conclusión

Este patrón permite crear listados consistentes, mantenibles y escalables sin modificar los componentes base del DataTable. La separación de responsabilidades entre backend (Fetcher/Scopes) y frontend (DataTable/Service) garantiza flexibilidad y reutilización.

**Recuerda:**
- ✅ **SÍ** crear Services, Builders, Scopes, Filtros y Table Services específicos por módulo
- ❌ **NO** modificar `resources/js/components/datatable/*`
- ✅ **SÍ** usar el patrón Fetcher para filtros, búsqueda y ordenamiento
- ✅ **SÍ** mantener consistencia en nomenclatura (`resourcePropName`, `field`, filtros)
- ✅ **SÍ** proporcionar slots `#filters` y `#responsive-card` para mejor UX
