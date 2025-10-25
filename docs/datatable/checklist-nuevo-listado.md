# Checklist para Implementar un Nuevo Listado Index

Esta checklist te guía paso a paso para crear un nuevo listado (ej: Nannies, Tutors, Bookings) usando el patrón DataTable.

---

## 📋 Pre-requisitos

- [ ] Modelo Eloquent existente (ej: `Nanny`)
- [ ] Rutas web definidas (resource routes o personalizadas)
- [ ] Tipos TypeScript definidos para el modelo (ej: `resources/js/types/Nanny.d.ts`)

---

## 🔧 Backend

### 1. Controller

**Archivo:** `app/Http/Controllers/NannyController.php`

- [ ] Crear método `index` que recibe el Service como dependency injection
- [ ] Llamar al método `indexFetch()` del Service
- [ ] Preparar datos adicionales (enums, opciones para filtros, etc.)
- [ ] Retornar vista con `Inertia::render()` pasando el resource y datos adicionales

**Template:**
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
        
        // Datos adicionales para filtros, etc.
        $disponibilidades = ['mañana', 'tarde', 'noche'];

        return Inertia::render('Nanny/Index', [
            'nannies' => $nannies,
            'disponibilidades' => $disponibilidades,
        ]);
    }
}
```

### 2. Service

**Archivo:** `app/Services/NannyService.php`

- [ ] Crear método `indexFetch(): LengthAwarePaginator`
- [ ] Iniciar query con relaciones necesarias (eager loading)
- [ ] Definir array `$sortables` con campos permitidos para ordenar
- [ ] Definir array `$searchables` con campos para búsqueda global
- [ ] Usar `Fetcher::for($query)` para aplicar filtros, búsqueda y ordenamiento
- [ ] Configurar `allowFilters()` con los filtros específicos
- [ ] Configurar `allowSort()` con campos ordenables
- [ ] Configurar `allowSearch()` con campos buscables
- [ ] Llamar a `paginate()` con número por defecto de items (ej: 12, 15)
- [ ] Retornar el `LengthAwarePaginator`

**Template:**
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
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(15);
    }
}
```

### 3. Builder (Si se necesitan Scopes personalizados)

**Archivo:** `app/Eloquent/Builders/NannyBuilder.php`

- [ ] Crear clase extendiendo `Illuminate\Database\Eloquent\Builder`
- [ ] Definir métodos públicos para cada scope personalizado
- [ ] Cada método debe usar `$this->tap(new ScopeClass($param))`
- [ ] Retornar `$this` para permitir chaining

**Template:**
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

- [ ] Registrar Builder en el Model:

```php
public function newEloquentBuilder($query): NannyBuilder
{
    return new NannyBuilder($query);
}
```

### 4. Scopes (Si se necesitan filtros complejos)

**Archivo:** `app/Scopes/Nanny/FiltrarPorDisponibilidad.php`

- [ ] Crear clase `readonly` con constructor que recibe el valor del filtro
- [ ] Implementar método `__invoke(Builder $query): Builder`
- [ ] Validar si el valor está vacío y retornar query sin cambios
- [ ] Aplicar lógica de filtrado al query
- [ ] Manejar arrays de valores si es necesario
- [ ] Retornar el query modificado

**Template:**
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

### 5. Model

**Archivo:** `app/Models/Nanny.php`

- [ ] Verificar que existan las relaciones necesarias (con métodos de relación)
- [ ] Si se creó un Builder personalizado, registrarlo con `newEloquentBuilder()`
- [ ] Asegurar que el modelo tenga los campos necesarios en `$fillable`

### 6. Contrato de Respuesta

- [ ] Verificar que el Service retorna un `LengthAwarePaginator` (no un `Collection`)
- [ ] Probar en Postman/Insomnia que la respuesta incluya:
  - `data` - Array de registros
  - `current_page` - Página actual
  - `per_page` - Items por página
  - `total` - Total de registros
  - `last_page` - Última página
  - `from`, `to` - Índices de registros
  - `links` - Links de paginación

---

## 🎨 Frontend

### 7. Page Component

**Archivo:** `resources/js/Pages/Nanny/Index.vue`

- [ ] Crear componente con `<script setup lang="ts">`
- [ ] Importar componentes necesarios: `Head`, `Link`, `Heading`, `Button`
- [ ] Importar tipos: `FetcherResponse`, modelo del recurso
- [ ] Importar componente de tabla específico: `NannyTable`
- [ ] Definir props con `defineProps<{ ... }>`
- [ ] Crear estructura básica:
  - Header con título e ícono
  - Botón para crear nuevo registro
  - Componente de tabla pasando resource y datos adicionales

**Template:**
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

### 8. Table Component

**Archivo:** `resources/js/Pages/Nanny/components/NannyTable.vue`

- [ ] Crear componente con `<script setup lang="ts">`
- [ ] Importar `DataTable` y `Column` de `@/components/datatable/`
- [ ] Importar tipos necesarios
- [ ] Importar componentes hijos: `*Card.vue`, `*Filtros.vue`
- [ ] Importar y instanciar el Table Service
- [ ] Definir props con el resource y datos adicionales
- [ ] Desestructurar propiedades del service

**Template mínimo:**
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
        <!-- Filtros -->
        <template #filters>
            <NannyFiltros v-model:filtros="filtros" :disponibilidades />
        </template>

        <!-- Card responsivo -->
        <template #responsive-card="{ slotProps }">
            <NannyCard :nanny="slotProps" />
        </template>

        <!-- Columnas (mínimo 3) -->
        <Column header="Nombre" field="user.name" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.user?.name ?? "—" }}
            </template>
        </Column>

        <Column header="Experiencia" field="experiencia" :sortable="true">
            <template #body="slotProps">
                {{ slotProps.record?.experiencia ?? 0 }} años
            </template>
        </Column>

        <Column header="Tarifa" field="tarifa" :sortable="true">
            <template #body="slotProps">
                ${{ slotProps.record?.tarifa ?? 0 }}
            </template>
        </Column>

        <!-- Columna de acciones -->
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

**Checklist de columnas:**
- [ ] Al menos 3 columnas definidas
- [ ] Una columna usa `#body` slot para renderizado personalizado
- [ ] Columnas ordenables tienen `:sortable="true"` y `field` coincide con backend
- [ ] Columna de acciones con handlers del service
- [ ] Manejo de valores vacíos con `?? "—"` o similar

### 9. Filtros Component

**Archivo:** `resources/js/Pages/Nanny/components/NannyFiltros.vue`

- [ ] Crear componente con `<script setup lang="ts">`
- [ ] Exportar interfaz TypeScript para los filtros
- [ ] Definir props necesarias (opciones de filtros)
- [ ] Usar `defineModel` para `v-model:filtros`
- [ ] Implementar inputs/selects para cada filtro
- [ ] Usar componentes de UI (`Select`, `Input`, etc.)

**Template:**
```vue
<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';

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
            <label class="mb-1 ml-1">Experiencia mínima</label>
            <Input v-model.number="filtros.experiencia_min" type="number" min="0" />
        </div>
    </div>
</template>
```

**Checklist:**
- [ ] Interfaz TypeScript exportada
- [ ] `v-model` configurado correctamente
- [ ] Labels descriptivos para cada filtro
- [ ] Placeholders útiles

### 10. Card Component (Responsive)

**Archivo:** `resources/js/Pages/Nanny/components/NannyCard.vue`

- [ ] Crear componente con `<script setup lang="ts">`
- [ ] Definir prop que recibe el registro completo
- [ ] Usar componentes `Card`, `CardHeader`, `CardContent` de UI
- [ ] Diseñar layout para móvil/tablet
- [ ] Incluir información clave del registro
- [ ] Agregar acciones (menú dropdown o botones)

**Template básico:**
```vue
<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card';
import type { Nanny } from '@/types/Nanny';

const props = defineProps<{
    nanny: Nanny;
}>();
</script>

<template>
    <Card class="relative overflow-hidden">
        <CardHeader class="flex flex-row gap-4">
            <div class="flex-1">
                <h3 class="font-semibold">
                    {{ props.nanny.user?.name }} {{ props.nanny.user?.surnames }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ props.nanny.user?.email }}
                </p>
            </div>
        </CardHeader>
        <CardContent>
            <p>Experiencia: {{ props.nanny.experiencia }} años</p>
            <p>Tarifa: ${{ props.nanny.tarifa }}</p>
        </CardContent>
    </Card>
</template>
```

### 11. Table Service

**Archivo:** `resources/js/services/nannyTableService.ts`

- [ ] Crear clase exportada `NannyTableService`
- [ ] Definir estado reactivo con `ref()`:
  - Filtros
  - Columnas visibles
  - Modales
  - Registros seleccionados para acciones
- [ ] Implementar constructor con `provide/inject` para filtros
- [ ] Crear método `getFilters()` que retorna objeto de filtros
- [ ] Crear handlers para acciones (ver, editar, eliminar)
- [ ] Crear helpers de presentación (clases de badges, formateos)

**Template:**
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
        'Experiencia',
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

**Checklist:**
- [ ] Estado reactivo con `ref()`
- [ ] `provide` configurado con nombre `{resourcePropName}_filters`
- [ ] Método `getFilters()` implementado
- [ ] Handlers para ver, editar, eliminar
- [ ] Método `clear_*_filters` en provide

---

## ✨ UX

### 12. Responsive Card

- [ ] Card component creado y pasado al slot `#responsive-card`
- [ ] Breakpoint configurado en DataTable (recomendado: `'lg'`)
- [ ] Card muestra información relevante
- [ ] Acciones accesibles desde el card (menú dropdown)

### 13. Badges/Estado

- [ ] Crear helper en service para clases de badges si aplica
- [ ] Usar componente `Badge` para estados/roles
- [ ] Definir colores consistentes para cada estado

### 14. Acciones por Fila

- [ ] Columna de acciones definida
- [ ] Al menos 2 acciones: editar y eliminar
- [ ] Íconos claros y descriptivos
- [ ] Tooltips con `title` attribute
- [ ] Hover states con clases CSS
- [ ] Modal de confirmación para eliminar

---

## 🧪 QA (Quality Assurance)

### 15. Paginación

- [ ] Navegar a página 2 y verificar que cambie la URL (`?page=2`)
- [ ] Verificar que se muestren los registros correctos
- [ ] Cambiar items por página y verificar funcionamiento
- [ ] Probar navegación con botones anterior/siguiente
- [ ] Verificar que los links de paginación funcionen

### 16. Ordenamiento (Sort)

- [ ] Click en header de columna ordenable
- [ ] Verificar que cambie la URL (`?sortBy=name&sortDirection=asc`)
- [ ] Verificar que los registros se ordenen correctamente
- [ ] Alternar entre ascendente/descendente
- [ ] Verificar que solo columnas con `:sortable="true"` sean ordenables

### 17. Filtros

- [ ] Abrir panel de filtros
- [ ] Seleccionar un valor en cada filtro
- [ ] Verificar que cambie la URL (`?filters[campo]=valor`)
- [ ] Verificar que se filtren los registros correctamente
- [ ] Combinar múltiples filtros
- [ ] Limpiar filtros y verificar que vuelvan todos los registros

### 18. Búsqueda

- [ ] Escribir en la barra de búsqueda
- [ ] Verificar que cambie la URL (`?searchTerm=texto`)
- [ ] Verificar que se encuentren registros coincidentes
- [ ] Probar búsqueda con caracteres especiales
- [ ] Verificar que búsqueda vacía muestre todos los registros

### 19. Sin Resultados

- [ ] Aplicar filtros que no devuelvan resultados
- [ ] Verificar que se muestre mensaje de "No hay registros"
- [ ] Verificar que no se muestren errores en consola
- [ ] Verificar que paginación se oculte o maneje correctamente

### 20. Errores

- [ ] Probar con ID inexistente en acciones (editar/ver)
- [ ] Verificar manejo de errores de red
- [ ] Verificar mensajes de error en formularios
- [ ] Verificar que no se muestren errores 500 en consola

### 21. Responsive

- [ ] Probar en móvil (< 768px)
- [ ] Verificar que se muestren cards en vez de tabla (si configurado)
- [ ] Verificar que acciones funcionen en cards
- [ ] Probar en tablet (768px - 1024px)
- [ ] Verificar que botones y controles sean accesibles

### 22. Performance

- [ ] Verificar que no haya N+1 queries (Laravel Debugbar)
- [ ] Verificar que eager loading esté configurado
- [ ] Verificar tiempos de respuesta aceptables (< 1s)
- [ ] Probar con dataset grande (> 100 registros)

---

## 📝 Documentación

### 23. Comentarios en Código

- [ ] Comentar lógica compleja en scopes
- [ ] Documentar métodos públicos en services si es necesario
- [ ] Agregar tipos TypeScript completos

### 24. Actualizar README (Opcional)

- [ ] Agregar sección del nuevo módulo al README del proyecto
- [ ] Documentar query params específicos si son únicos
- [ ] Documentar filtros personalizados complejos

---

## ✅ Verificación Final

### 25. Checklist de Verificación

- [ ] ✅ Backend retorna `FetcherResponse<T>` con paginación correcta
- [ ] ✅ Filtros funcionan y se reflejan en URL
- [ ] ✅ Ordenamiento funciona en columnas marcadas como sortable
- [ ] ✅ Búsqueda encuentra registros correctamente
- [ ] ✅ Paginación navega entre páginas correctamente
- [ ] ✅ Columnas visibles se pueden ocultar/mostrar
- [ ] ✅ Vista responsive con cards funciona en móvil
- [ ] ✅ Acciones (ver/editar/eliminar) funcionan correctamente
- [ ] ✅ No hay errores en consola del navegador
- [ ] ✅ No hay errores en logs de Laravel
- [ ] ✅ No hay N+1 queries
- [ ] ✅ URLs son compartibles (mismos filtros/página al copiar URL)
- [ ] ✅ Navegación con botones back/forward del navegador funciona

---

## 🚀 Deploy

### 26. Pre-Deploy

- [ ] Ejecutar tests si existen
- [ ] Verificar que no haya archivos temporales commiteados
- [ ] Verificar que migraciones estén actualizadas
- [ ] Verificar que seeders incluyan datos para pruebas

### 27. Post-Deploy

- [ ] Probar en ambiente de staging
- [ ] Verificar performance en producción
- [ ] Monitorear logs de errores
- [ ] Recolectar feedback de usuarios

---

## 📚 Referencias

- **Patrón completo:** `docs/datatable/patron-index-listados.md`
- **API del DataTable:** `resources/js/components/datatable/README.md`
- **Ejemplo completo:** `resources/js/Pages/User/` (Users implementation)

---

## 💡 Tips

1. **Copia del ejemplo de Users:** Es más rápido copiar y adaptar los archivos de Users que empezar desde cero.
2. **Usa snippets:** Guarda templates de Service, Builder, Scope como snippets en tu editor.
3. **Mantén consistencia:** Usa los mismos nombres de convención (plurales, PascalCase, etc.).
4. **Prueba iterativamente:** No implementes todo de golpe. Empieza con backend → page → tabla básica → filtros.
5. **Revisa logs:** Usa Laravel Telescope o Debugbar para identificar problemas de queries.

---

## ❌ Errores Comunes a Evitar

1. ❌ Olvidar registrar el Builder en el Model
2. ❌ No coincidir `Column.field` con backend `$sortables`
3. ❌ No usar `provide/inject` correctamente para filtros
4. ❌ Retornar `Collection` en vez de `LengthAwarePaginator`
5. ❌ No manejar valores `null` en templates (`?? "—"`)
6. ❌ Olvidar el `with()` para eager loading
7. ❌ No validar campos ordenables (whitelist en backend)
8. ❌ Usar nombre incorrecto en `resourcePropName`

---

¡Felicidades! 🎉 Si completaste todos los pasos, tu nuevo listado debería estar funcionando correctamente siguiendo el patrón DataTable.
