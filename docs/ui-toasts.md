# Toast Notifications System

Este documento describe el sistema de notificaciones toast implementado en la aplicación Sweet Nanny.

## Descripción General

El sistema de toasts utiliza **vue-sonner** para mostrar notificaciones temporales en la esquina superior derecha de la pantalla. Los toasts se disparan automáticamente desde mensajes flash del backend o manualmente desde el frontend.

## Características

- **Posición**: Superior derecha (`top-right`)
- **Tipos**: Success, Error, Warning, Info
- **Duración**: 5-6 segundos con pausa al hacer hover
- **Estilo**: Rich colors con botón de cerrar
- **Accesibilidad**: Soporte completo con ARIA attributes
- **Deduplicación**: Previene mostrar el mismo mensaje múltiples veces

## Uso desde el Backend (Laravel)

### Método Principal: Flash Messages

En tus controladores, puedes enviar mensajes usando el método `with()`:

#### 1. Mensaje con Título y Descripción (formato actual)

```php
return redirect()->route('users.index')->with('message', [
    'title' => 'Usuario creado',
    'description' => 'El usuario ha sido creado correctamente.',
]);
```

Este formato se mantiene por compatibilidad y se muestra como un toast de éxito.

#### 2. Mensajes Directos por Tipo

```php
// Success
return redirect()->back()->with('success', 'Operación exitosa.');

// Error
return redirect()->back()->with('error', 'Ocurrió un error.');

// Warning
return redirect()->back()->with('warning', 'Atención: verifica los datos.');

// Info
return redirect()->back()->with('info', 'Información importante.');

// Status (tratado como success)
return back()->with('status', 'verification-link-sent');
```

### Ejemplos Prácticos

#### Crear un Recurso

```php
public function store(CreateRequest $request)
{
    $resource = Resource::create($request->validated());
    
    return redirect()->route('resources.index')
        ->with('success', 'Recurso creado correctamente.');
}
```

#### Actualizar un Recurso

```php
public function update(UpdateRequest $request, Resource $resource)
{
    $resource->update($request->validated());
    
    return redirect()->route('resources.index')
        ->with('message', [
            'title' => 'Recurso actualizado',
            'description' => 'Los cambios se han guardado correctamente.',
        ]);
}
```

#### Eliminar un Recurso

```php
public function destroy(Resource $resource)
{
    $resource->delete();
    
    return redirect()->back()
        ->with('success', 'Recurso eliminado correctamente.');
}
```

#### Manejo de Errores

```php
public function process(Request $request)
{
    try {
        // Lógica de procesamiento
        return redirect()->back()
            ->with('success', 'Procesado correctamente.');
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al procesar: ' . $e->getMessage());
    }
}
```

## Uso desde el Frontend (Vue 3)

### Composable `useToast()`

Importa y usa el composable en cualquier componente Vue:

```vue
<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { success, error, warning, info, toast } = useToast();

const handleAction = () => {
    // Mensaje simple
    success('Acción completada');
    
    // Mensaje con descripción
    success('Usuario creado', 'El usuario ha sido creado correctamente.');
};

const handleError = () => {
    error('Error al guardar', 'Por favor, intenta nuevamente.');
};

const handleWarning = () => {
    warning('Datos incompletos', 'Algunos campos requieren atención.');
};

const handleInfo = () => {
    info('Información', 'Recuerda guardar los cambios.');
};
</script>
```

### API del Composable

```typescript
interface UseToast {
    success(message: string, description?: string): void;
    error(message: string, description?: string): void;
    warning(message: string, description?: string): void;
    info(message: string, description?: string): void;
    toast: ToastFunction; // Para uso avanzado
}
```

### Uso Avanzado

Para opciones más avanzadas, usa directamente la función `toast`:

```typescript
import { useToast } from '@/composables/useToast';

const { toast } = useToast();

// Toast con acción
toast.success('Cambios guardados', {
    description: '¿Quieres ir a la lista?',
    action: {
        label: 'Ver lista',
        onClick: () => router.visit('/resources')
    },
    duration: 10000
});

// Toast con promesa
toast.promise(
    fetch('/api/data'),
    {
        loading: 'Cargando...',
        success: 'Datos cargados',
        error: 'Error al cargar'
    }
);
```

## Integración Automática

### Sistema de Deduplicación

El sistema previene mostrar el mismo mensaje múltiples veces usando:

1. Cache temporal de mensajes mostrados (10 segundos)
2. Identificadores únicos por tipo y contenido
3. Limpieza automática del cache

### Layouts con Soporte

Los siguientes layouts tienen integración automática de toasts:

- `AppLayout.vue` - Layout principal de la aplicación
- `AuthLayout.vue` - Layout de autenticación

Cualquier mensaje flash enviado desde el backend se mostrará automáticamente como toast en estos layouts.

## Configuración

### Posición del Toaster

Configurado en `app.ts`:

```typescript
h(Toaster, {
    position: 'top-right',      // Posición en la esquina superior derecha
    richColors: true,            // Colores ricos por tipo
    closeButton: true,           // Botón de cerrar visible
    duration: 5000,              // Duración por defecto: 5 segundos
    pauseWhenPageIsHidden: true  // Pausa cuando la página está oculta
})
```

### Duración por Tipo

- Success: 5 segundos
- Error: 6 segundos (más tiempo para leer)
- Warning: 5 segundos
- Info: 5 segundos

## Estilos y Tema

Los toasts usan las variables CSS del tema actual:

```css
--normal-bg: var(--popover)
--normal-text: var(--popover-foreground)
--normal-border: var(--border)
```

El tema oscuro/claro se aplica automáticamente según la configuración del usuario.

## Mejores Prácticas

### Backend

1. **Sé específico**: Usa mensajes claros y descriptivos
2. **Usa el tipo correcto**: 
   - `success` para acciones completadas
   - `error` para errores
   - `warning` para advertencias
   - `info` para información general
3. **Consistencia**: Mantén un estilo de mensajes consistente en toda la aplicación
4. **Brevedad**: Mensajes cortos y concisos (el toast se cierra automáticamente)

### Frontend

1. **Feedback inmediato**: Muestra toasts inmediatamente después de acciones del usuario
2. **No abuses**: Evita múltiples toasts simultáneos
3. **Contexto**: Proporciona suficiente contexto en la descripción
4. **Acciones opcionales**: Usa el toast avanzado para acciones relacionadas

## Migración de Código Existente

### Antes (sin toasts)

```php
// Sin feedback visual
return redirect()->back();
```

### Después (con toasts)

```php
// Con feedback visual apropiado
return redirect()->back()
    ->with('success', 'Acción completada correctamente.');
```

## Solución de Problemas

### El toast no aparece

1. Verifica que el mensaje se envía correctamente desde el backend
2. Confirma que el layout usado incluye el watcher de flash
3. Revisa la consola del navegador para errores
4. Asegúrate de que el tipo de mensaje es correcto (`success`, `error`, etc.)

### Múltiples toasts del mismo mensaje

El sistema de deduplicación debería prevenir esto. Si ocurre:

1. Verifica que no estás enviando el mismo mensaje en múltiples lugares
2. Revisa el código de navegación (evita recargas innecesarias)
3. Usa `replace: true` en navegaciones Inertia cuando sea apropiado

### Los estilos no se aplican correctamente

1. Verifica que las variables CSS del tema estén definidas
2. Confirma que el modo oscuro/claro funciona correctamente
3. Revisa que no haya conflictos CSS con otros componentes

## Recursos Adicionales

- [vue-sonner Documentation](https://vue-sonner.vercel.app/)
- [Inertia.js Flash Messages](https://inertiajs.com/redirects#flash-messages)
- [Laravel Session Flash](https://laravel.com/docs/session#flash-data)
