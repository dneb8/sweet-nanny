# Sistema de Notificaciones Toast

Sistema unificado de notificaciones toast con soporte para **Iconify**, variantes de color y múltiples fuentes de eventos (flash messages de Laravel + notificaciones en tiempo real).

## 📋 Tabla de Contenidos

- [Introducción](#introducción)
- [Arquitectura](#arquitectura)
- [API y Uso](#api-y-uso)
- [Variantes y Estilos](#variantes-y-estilos)
- [Integración con Laravel](#integración-con-laravel)
- [Notificaciones en Tiempo Real](#notificaciones-en-tiempo-real)
- [Ejemplos](#ejemplos)
- [Troubleshooting](#troubleshooting)

---

## Introducción

El sistema de notificaciones toast está construido sobre **vue-sonner** con una capa de personalización que permite:

- ✅ Íconos personalizados con **Iconify**
- ✅ Variantes de color (success, info, warning, error)
- ✅ Títulos y descripciones
- ✅ Duración configurable
- ✅ Unificación de múltiples fuentes de eventos

---

## Arquitectura

### Componentes Principales

1. **`useNotify`** (`resources/js/composables/useNotify.ts`)
   - Composable principal que expone la API de notificaciones
   - Maneja renderizado personalizado con Iconify
   - Define variantes y esquemas de color

2. **`useFlashMessages`** (`resources/js/composables/useFlashMessages.ts`)
   - Integra flash messages de Laravel con el sistema de toasts
   - Escucha `page.props.flash` de Inertia.js
   - Previene duplicados con sistema de deduplicación

3. **`useNotifications`** (`resources/js/composables/useNotifications.ts`)
   - Maneja notificaciones en tiempo real (Laravel Echo/Pusher)
   - Integra con el sistema de toasts
   - Gestiona estado de notificaciones (leídas/no leídas)

4. **`Toaster`** (montado en `app.ts`)
   - Componente de vue-sonner montado globalmente
   - Renderiza todos los toasts de la aplicación

---

## API y Uso

### Importación

```typescript
import { useNotify } from '@/composables/useNotify';
```

### API Básica

```typescript
const { notify, notifySuccess, notifyInfo, notifyWarning, notifyError } = useNotify();

// Notificación personalizada completa
notify({
    variant: 'success',
    title: 'Operación exitosa',
    description: 'Los cambios se guardaron correctamente',
    icon: 'mdi:check-circle', // Opcional, usa ícono por defecto de la variante
    duration: 5000, // Opcional, 5000ms por defecto
});

// Helpers de variantes
notifySuccess('Perfil actualizado', 'Tus cambios se guardaron correctamente');
notifyInfo('Procesando', 'Tu imagen está siendo validada');
notifyWarning('Atención', 'Este campo es obligatorio');
notifyError('Error', 'No se pudo completar la operación');
```

### Opciones de `notify()`

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `variant` | `'success' \| 'info' \| 'warning' \| 'error'` | ✅ | Variante de color |
| `title` | `string` | ✅ | Título del toast |
| `description` | `string` | ❌ | Descripción adicional (opcional) |
| `icon` | `string` | ❌ | Ícono de Iconify (usa predeterminado si se omite) |
| `duration` | `number` | ❌ | Duración en ms (5000 por defecto) |

### Helpers Simplificados

```typescript
// Solo título
notifySuccess('Operación exitosa');

// Título + descripción
notifyInfo('Procesando', 'Espera unos segundos...');

// Título + descripción + ícono personalizado
notifyWarning('Advertencia', 'Verifica los datos', 'mdi:alert-octagon');

// Título + descripción + ícono + duración
notifyError('Error crítico', 'Contacta soporte', 'mdi:alert-circle', 10000);
```

---

## Variantes y Estilos

### Variantes Disponibles

| Variante | Color | Ícono por Defecto | Uso |
|----------|-------|-------------------|-----|
| `success` | Verde (emerald) | `mdi:check-circle` | Operaciones exitosas |
| `info` | Azul (sky) | `mdi:information-outline` | Información general |
| `warning` | Amarillo (amber) | `mdi:alert` | Advertencias |
| `error` | Rojo (rose) | `mdi:close-circle` | Errores |

### Esquemas de Color

Los toasts usan clases de Tailwind CSS con soporte de modo oscuro:

```typescript
// Ejemplo: Variante success
'bg-emerald-50 text-emerald-800 border-emerald-200 
 dark:bg-emerald-900/20 dark:text-emerald-100 dark:border-emerald-800'
```

Cada variante tiene:
- **Fondo** con opacidad en modo oscuro
- **Texto** contrastante
- **Borde** sutil
- **Ícono** con color específico de la variante

---

## Integración con Laravel

### Flash Messages

El sistema escucha automáticamente los flash messages de Laravel:

```php
// En el controlador
return redirect()->route('profile')
    ->with('success', 'Perfil actualizado correctamente');

return back()
    ->with('error', 'No se pudo completar la operación');

return to_route('settings')
    ->with('info', 'Tu imagen está siendo validada');

return back()
    ->with('warning', 'Verifica los campos requeridos');
```

### Flash Messages con Descripción

```php
return redirect()->route('home')->with('success', [
    'title' => 'Usuario creado',
    'description' => 'El usuario se creó correctamente',
    'icon' => 'mdi:account-check', // Opcional
]);
```

### Claves Soportadas

El sistema escucha las siguientes claves en `page.props.flash`:
- `success` o `message` → Toast de éxito
- `error` → Toast de error
- `warning` → Toast de advertencia
- `info` o `status` → Toast informativo

---

## Notificaciones en Tiempo Real

### Configuración

Las notificaciones en tiempo real se integran automáticamente cuando usas `useNotifications`:

```typescript
import { useNotifications } from '@/composables/useNotifications';

const { handleNewNotification } = useNotifications();

// En tu componente/layout con Laravel Echo
Echo.private(`App.Models.User.${userId}`)
    .notification((notification) => {
        handleNewNotification(notification.data);
    });
```

### Formato de Datos

```typescript
// Ejemplo de payload de notificación
{
    type: 'avatar',
    success: true,
    message: '¡Tu foto de perfil ha sido aprobada!',
    redirect: '/settings/profile'
}
```

### Tipos de Notificaciones

| Tipo | Variante Toast | Ícono |
|------|----------------|-------|
| `avatar` + `success: true` | Success | `mdi:check-circle` |
| `avatar` + `success: false` | Error | `mdi:alert-circle` |
| Otros tipos | Info | `mdi:bell` |

---

## Ejemplos

### Ejemplo 1: Upload de Avatar

```typescript
import { useNotify } from '@/composables/useNotify';

const { notifyInfo, notifyError } = useNotify();

const uploadAvatar = () => {
    form.post('/settings/profile/avatar', {
        onSuccess: () => {
            notifyInfo(
                'Imagen subida',
                'Te notificaremos cuando sea aprobada',
                'mdi:cloud-upload',
                5000
            );
        },
        onError: (errors) => {
            notifyError(
                'Error al subir imagen',
                errors?.avatar ?? 'Por favor, intenta nuevamente',
                'mdi:alert-circle'
            );
        },
    });
};
```

### Ejemplo 2: Crear Usuario

```php
// Controller
public function store(Request $request)
{
    $user = User::create($request->validated());
    
    return redirect()->route('users.index')
        ->with('success', 'Usuario creado correctamente');
}
```

El toast se muestra automáticamente gracias a `useFlashMessages`.

### Ejemplo 3: Notificación Personalizada

```typescript
import { useNotify } from '@/composables/useNotify';

const { notify } = useNotify();

// Toast de éxito personalizado
notify({
    variant: 'success',
    title: 'Proceso completado',
    description: 'Todos los registros se actualizaron',
    icon: 'mdi:database-check',
    duration: 7000,
});

// Toast de advertencia con ícono personalizado
notify({
    variant: 'warning',
    title: 'Límite de almacenamiento',
    description: 'Has usado el 90% de tu espacio',
    icon: 'mdi:harddisk-alert',
    duration: 10000,
});
```

### Ejemplo 4: Manejo de Errores de Formulario

```typescript
const { notifyError } = useNotify();

form.post('/api/users', {
    onError: (errors) => {
        const firstError = Object.values(errors)[0];
        notifyError(
            'Error de validación',
            firstError as string,
            'mdi:form-textbox-alert'
        );
    },
});
```

---

## Troubleshooting

### Los toasts no se muestran

**Problema**: Los toasts no aparecen al enviar flash messages desde Laravel.

**Solución**:
1. Verifica que `FlashMessagesHandler` esté montado en `app.ts`:
   ```typescript
   h(FlashMessagesHandler)
   ```

2. Confirma que `Toaster` de vue-sonner esté montado:
   ```typescript
   h(Toaster)
   ```

3. Revisa la consola del navegador para errores.

---

### Toasts duplicados

**Problema**: Se muestran múltiples toasts para la misma acción.

**Solución**:
- El sistema incluye deduplicación automática con un `Set<string>`.
- Si usas llamadas manuales a `notify()`, asegúrate de no llamarlo múltiples veces.
- En Profile.vue se usa un flag `avatarToastShown` para evitar duplicados en la misma sesión.

---

### Íconos no se muestran

**Problema**: Los íconos de Iconify no aparecen.

**Solución**:
1. Verifica que `@iconify/vue` esté instalado:
   ```bash
   npm list @iconify/vue
   ```

2. Confirma que el ícono existe en Iconify: https://icon-sets.iconify.design/

3. Usa el formato correcto: `prefijo:nombre` (ej: `mdi:check-circle`)

---

### Estilos no se aplican correctamente

**Problema**: Los toasts no tienen los colores correctos.

**Solución**:
1. Verifica que Tailwind CSS esté configurado correctamente.
2. Asegúrate de que las clases de color (`emerald`, `sky`, `amber`, `rose`) estén disponibles.
3. Revisa que el modo oscuro esté funcionando (clases `dark:`).

---

### Duración incorrecta

**Problema**: Los toasts desaparecen demasiado rápido o muy lento.

**Solución**:
- Pasa el parámetro `duration` en milisegundos:
  ```typescript
  notifySuccess('Título', 'Descripción', undefined, 8000); // 8 segundos
  ```

- Por defecto, la duración es de **5000ms (5 segundos)**.

---

### Conflicto con toasts antiguos

**Problema**: Hay conflicto entre sistemas de toast (shadcn-vue vs vue-sonner).

**Solución**:
- Este sistema usa **únicamente vue-sonner** con `toast.custom()`.
- Si encuentras imports de `@/components/ui/toast/use-toast`, reemplázalos con `useNotify`.
- Elimina cualquier `<Toast />` o `<Toaster />` de shadcn-vue del proyecto.

---

## Buenas Prácticas

### ✅ Hacer

- Usa helpers (`notifySuccess`, `notifyError`, etc.) para casos simples
- Proporciona descripciones cuando el contexto es importante
- Usa íconos personalizados cuando mejoren la comprensión
- Ajusta la duración según la importancia del mensaje

### ❌ Evitar

- No uses toasts para información crítica que deba permanecer visible
- No abuses de duraciones muy largas (>10 segundos)
- No uses toasts para confirmar acciones destructivas (usa modales)
- No muestres toasts genéricos sin contexto

---

## Roadmap

- [ ] Soporte para acciones en toasts (botones)
- [ ] Posicionamiento personalizable
- [ ] Animaciones personalizadas
- [ ] Soporte para toasts persistentes
- [ ] Agrupación de toasts similares
- [ ] Historial de notificaciones

---

## Recursos

- **vue-sonner**: https://vue-sonner.vercel.app/
- **Iconify**: https://icon-sets.iconify.design/
- **Material Design Icons**: https://icon-sets.iconify.design/mdi/
- **Tailwind CSS**: https://tailwindcss.com/docs

---

## Contribuir

Si encuentras bugs o tienes sugerencias de mejora, abre un issue o PR en el repositorio.
