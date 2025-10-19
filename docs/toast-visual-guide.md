# Toast Notifications Visual Guide

This guide provides examples of how toast notifications will appear in the Sweet Nanny application.

## Toast Positions and Appearance

### Position
All toasts appear in the **top-right corner** of the screen, floating above all other content.

### Duration
- **Success/Info/Warning**: 5 seconds
- **Error**: 6 seconds (more time to read error messages)
<!-- Pause on Hover: Toasts do not currently pause when you hover over them -->

### Close Button
All toasts have a visible close button (×) in the top-right corner for manual dismissal.

## Toast Types

### 1. Success Toast 🟢

**Appearance**: Green background with white text

**When to use**: 
- Successful operations
- Completed actions
- Confirmations

**Examples**:
```
✓ Usuario creado correctamente
✓ Perfil actualizado correctamente
✓ Contraseña actualizada correctamente
```

**Backend code**:
```php
return redirect()->back()->with('success', 'Usuario creado correctamente.');
```

**Frontend code**:
```typescript
const { success } = useToast();
success('Usuario creado correctamente');
```

---

### 2. Error Toast 🔴

**Appearance**: Red background with white text

**When to use**:
- Errors and failures
- Failed operations
- Critical issues

**Examples**:
```
✗ Error al guardar los datos
✗ No se pudo completar la operación
✗ Error de conexión con el servidor
```

**Backend code**:
```php
return redirect()->back()->with('error', 'Error al guardar los datos.');
```

**Frontend code**:
```typescript
const { error } = useToast();
error('Error al guardar los datos');
```

---

### 3. Warning Toast 🟡

**Appearance**: Yellow/orange background with dark text

**When to use**:
- Warnings and cautions
- Potential issues
- Important notices

**Examples**:
```
⚠ Algunos campos están incompletos
⚠ Esta acción no se puede deshacer
⚠ Verifica los datos antes de continuar
```

**Backend code**:
```php
return redirect()->back()->with('warning', 'Algunos campos están incompletos.');
```

**Frontend code**:
```typescript
const { warning } = useToast();
warning('Algunos campos están incompletos');
```

---

### 4. Info Toast 🔵

**Appearance**: Blue background with white text

**When to use**:
- Informational messages
- Tips and hints
- Status updates

**Examples**:
```
ℹ Los cambios se guardarán automáticamente
ℹ Puedes editar este campo más tarde
ℹ Tienes 3 notificaciones nuevas
```

**Backend code**:
```php
return redirect()->back()->with('info', 'Los cambios se guardarán automáticamente.');
```

**Frontend code**:
```typescript
const { info } = useToast();
info('Los cambios se guardarán automáticamente');
```

---

### 5. Success with Description

**Appearance**: Success toast with additional description text below the title

**When to use**: When you need to provide more context about the success

**Example**:
```
✓ Usuario creado
  El usuario ha sido creado correctamente y se ha enviado un email de bienvenida.
```

**Backend code**:
```php
return redirect()->back()->with('message', [
    'title' => 'Usuario creado',
    'description' => 'El usuario ha sido creado correctamente y se ha enviado un email de bienvenida.',
]);
```

**Frontend code**:
```typescript
const { success } = useToast();
success('Usuario creado', 'El usuario ha sido creado correctamente.');
```

## Visual Layout

```
┌──────────────────────────────────────────────┐
│ Screen Content                               │
│                                              │
│                              ┌──────────────┐│
│                              │ ✓ Success!  ×││
│                              │ Desc. here   ││
│                              └──────────────┘│
│                                              │
│                                              │
│  Main content continues here...              │
│                                              │
└──────────────────────────────────────────────┘
```

## Dark Mode Support

All toast notifications automatically adapt to the current theme:

**Light Mode**:
- Success: Green (#10b981)
- Error: Red (#ef4444)
- Warning: Orange (#f59e0b)
- Info: Blue (#3b82f6)

**Dark Mode**:
- Success: Lighter green
- Error: Lighter red
- Warning: Lighter orange
- Info: Lighter blue

The contrast is automatically adjusted to ensure readability in both themes.

## Responsive Behavior

### Desktop (> 768px)
- Toasts appear in top-right corner
- Width: ~400px
- Margin from edges: 16px

### Tablet (768px - 480px)
- Toasts appear in top-right corner
- Width: ~350px
- Margin from edges: 12px

### Mobile (< 480px)
- Toasts appear at the top center
- Width: calc(100% - 32px)
- Margin from edges: 16px

## Animation

### Entrance
- Slides in from the right
- Duration: 200ms
- Easing: ease-out

### Exit
- Fades out and slides right
- Duration: 200ms
- Easing: ease-in

### Stacking
When multiple toasts appear:
- New toasts appear above existing ones
- Each toast pushes previous ones down
- Maximum visible: 3 toasts (older ones dismissed automatically)

## Interaction

### Hover
- Toast pauses auto-dismiss timer
- Background slightly darkens
- Cursor changes to indicate interactivity

### Click Close Button
- Immediate dismissal
- Fade-out animation (100ms)
- Next toast moves up to fill space

### Auto-dismiss
- After 5-6 seconds (depending on type)
- Fade-out animation
- Next toast moves up smoothly

## Accessibility

### Screen Readers
- Toasts announced as "alert" or "status" based on type
- Content read automatically when toast appears
- Dismiss button labeled "Close notification"

### Keyboard Navigation
- Close button is keyboard accessible
- Tab to focus on close button
- Enter or Space to dismiss

### Motion
- Respects `prefers-reduced-motion` setting
- Animations disabled if user prefers reduced motion

## Best Practices

### ✅ DO

1. **Be Concise**: Keep messages short and clear
   ```
   ✓ Usuario creado
   ```

2. **Use Appropriate Type**: Match the toast type to the message
   ```
   ✗ Error al guardar  (use error type, not info)
   ```

3. **Provide Context**: Add descriptions for complex operations
   ```
   ✓ Operación completada
     Los cambios se aplicarán en 5 minutos.
   ```

4. **Be Specific**: Tell users exactly what happened
   ```
   ✓ Contraseña actualizada correctamente
   ```

### ❌ DON'T

1. **Don't Be Vague**: Avoid unclear messages
   ```
   ✗ Acción realizada  (what action?)
   ```

2. **Don't Overuse**: Not every action needs a toast
   ```
   ✗ Página cargada (unnecessary)
   ```

3. **Don't Be Too Long**: Keep messages brief
   ```
   ✗ El usuario Juan Pérez con email juan@example.com ha sido creado 
     exitosamente en el sistema y se ha enviado una notificación por 
     correo electrónico a su dirección registrada...
   ```

4. **Don't Stack Too Many**: One action = one toast
   ```
   ✗ Multiple toasts for the same action
   ```

## Common Use Cases

### CRUD Operations

#### Create
```php
return redirect()->route('users.index')
    ->with('success', 'Usuario creado correctamente.');
```

#### Update
```php
return redirect()->route('users.index')
    ->with('success', 'Usuario actualizado correctamente.');
```

#### Delete
```php
return redirect()->back()
    ->with('success', 'Usuario eliminado correctamente.');
```

### Form Submissions

#### Success
```php
return redirect()->back()
    ->with('success', 'Formulario enviado correctamente.');
```

#### Validation Error
```php
return redirect()->back()
    ->withErrors($validator)
    ->with('error', 'Por favor, corrige los errores del formulario.');
```

### Authentication

#### Login Success
```php
return redirect()->intended('dashboard')
    ->with('success', 'Bienvenido de nuevo!');
```

#### Password Reset
```php
return redirect()->route('login')
    ->with('status', 'Tu contraseña ha sido restablecida.');
```

#### Email Verification
```php
return back()
    ->with('status', 'verification-link-sent');
```

## Testing Checklist

To verify toast notifications are working correctly:

- [ ] Create a user → See success toast
- [ ] Update a user → See success toast
- [ ] Delete a user → See success toast
- [ ] Update password → See success toast
- [ ] Update profile → See success toast
- [ ] Cause validation error → See error toast
- [ ] Request password reset → See status toast
- [ ] Request email verification → See status toast
- [ ] Test in dark mode → Toasts styled correctly
- [ ] Test on mobile → Toasts positioned correctly
- [ ] Hover over toast → Toast pauses
- [ ] Click close button → Toast dismisses
- [ ] Wait for auto-dismiss → Toast fades out
- [ ] Create multiple actions quickly → No duplicate toasts

## Troubleshooting

### Toast doesn't appear
1. Check browser console for errors
2. Verify flash message is being sent from backend
3. Confirm layout includes flash message watcher
4. Test with browser DevTools Network tab to see Inertia response

### Toast appears twice
1. Check for duplicate `with()` calls in controller
2. Verify deduplication logic is working
3. Check for multiple watchers in nested layouts

### Wrong toast type
1. Verify correct `with()` key in backend (`success`, `error`, etc.)
2. Check HandleInertiaRequests middleware sharing
3. Test with different message types

### Styling issues
1. Verify CSS variables are defined
2. Check for CSS conflicts with custom styles
3. Test in both light and dark modes
4. Clear browser cache and rebuild assets

## Future Enhancements

Potential improvements for the toast system:

1. **Action Buttons**: Add action buttons for quick navigation
2. **Toast Icons**: Custom icons per message type
3. **Sound Effects**: Optional sound for important notifications
4. **Progress Bar**: Visual timer showing remaining time
5. **Toast Groups**: Group related toasts together
6. **Undo Actions**: Add undo functionality for destructive actions
7. **Custom Themes**: Allow per-toast theme overrides

---

**Note**: Screenshots will be added once the application is running with the toast system enabled.
