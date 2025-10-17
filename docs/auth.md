# Autenticación: Verificación de Email y Recuperación de Contraseña

Este documento describe la implementación de la verificación de correo electrónico y el sistema de recuperación de contraseña en Sweet Nanny.

## 📋 Tabla de Contenidos

- [Descripción General](#descripción-general)
- [Emails en Español con Logo y Tema Personalizado](#emails-en-español-con-logo-y-tema-personalizado)
- [Verificación de Email](#verificación-de-email)
- [Recuperación de Contraseña](#recuperación-de-contraseña)
- [Configuración](#configuración)
- [Pruebas](#pruebas)
- [Preguntas Frecuentes](#preguntas-frecuentes)

## Descripción General

Sweet Nanny implementa autenticación completa con:

- **Verificación de email** mediante enlaces firmados
- **Recuperación de contraseña** con tokens seguros
- **Throttling** (limitación de tasa) para prevenir abuso
- **Middleware de verificación** para proteger rutas sensibles
- **Emails en español** con logo embebido y tema personalizado

## Emails en Español con Logo y Tema Personalizado

### Logo Embebido con CID

Los emails de autenticación incluyen el logo de Sweet Nanny embebido inline usando CID (Content-ID). Esto garantiza que el logo se muestre correctamente incluso si el cliente de correo bloquea imágenes externas.

**Ubicación del logo:** `public/images/logo-email.png`
- Tamaño recomendado: 512x512px
- Formato: PNG con fondo transparente
- Embebido automáticamente en todos los emails de autenticación

**Implementación técnica:**

En `AppServiceProvider`, se usa `withSymfonyMessage()` para embedder el logo:

```php
$mail->withSymfonyMessage(function (\Symfony\Component\Mime\Email $email) {
    $logoPath = public_path('images/logo-email.png');
    if (file_exists($logoPath)) {
        $email->embedFromPath($logoPath, 'logo_cid');
    }
});
```

En las plantillas de email, se referencia usando CID:

```blade
<img src="cid:logo_cid" alt="Sweet Nanny" class="email-logo">
```

### Tema Personalizado

Los emails usan un tema personalizado (`sweetnanny`) con una paleta de colores consistente con el sitio:

**Colores principales:**
- Primary: `#8B5CF6` (púrpura)
- Primary Hover: `#7C3AED` (púrpura oscuro)
- Accent: `#F472B6` (rosa)
- Background: `#FDF7FF` (lavanda claro)
- Text: `#374151` (gris oscuro)

**Configuración:**

El tema está configurado en `config/mail.php`:

```php
'markdown' => [
    'theme' => 'sweetnanny',
    'paths' => [
        resource_path('views/vendor/mail'),
    ],
],
```

**Archivo de tema:** `resources/views/vendor/mail/html/themes/sweetnanny.css`

### Plantillas en Español

- **Verificación**: `resources/views/emails/auth/verify-es.blade.php`
  - Asunto: "Verifica tu correo"
  - Contenido completo en español
  
- **Reset de contraseña**: `resources/views/emails/auth/reset-es.blade.php`
  - Asunto: "Restablece tu contraseña"
  - Contenido completo en español

## Verificación de Email

### ¿Cómo Funciona?

1. **Registro**: Al registrarse, el usuario recibe un email en español con logo embebido
2. **Enlace Firmado**: El enlace contiene un hash firmado que expira después de 60 minutos
3. **Verificación**: Al hacer clic, el usuario es verificado y redirigido al dashboard
4. **Protección**: Las rutas protegidas requieren email verificado para acceder

### Implementación Técnica

#### Modelo User

El modelo `User` implementa la interfaz `MustVerifyEmail`:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

Esto activa automáticamente:
- El campo `email_verified_at` en la base de datos
- Los métodos `hasVerifiedEmail()` y `markEmailAsVerified()`
- El envío automático de notificaciones de verificación

#### Rutas de Verificación

Definidas en `routes/auth.php`:

```php
// Mostrar aviso de verificación
Route::get('verify-email', EmailVerificationPromptController::class)
    ->middleware('auth')
    ->name('verification.notice');

// Verificar email (enlace del correo)
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

// Reenviar email de verificación
Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
```

**Nota sobre el Throttling**: 
- Limitado a 6 intentos por minuto
- Previene spam y abuso del sistema de envío de emails

#### Middleware `verified`

Protege rutas que requieren email verificado:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas protegidas
});
```

Aplicado en:
- `/users` - Gestión de usuarios
- `/nannies` - Gestión de niñeras
- `/courses` - Gestión de cursos
- `/addresses` - Gestión de direcciones

Si un usuario no verificado intenta acceder, es redirigido a `/verify-email`.

#### Páginas Vue

- **`auth/VerifyEmail.vue`**: Muestra aviso para verificar email con botón para reenviar
- Integración con Inertia.js para SPA fluido

### Reenvío de Verificación

Los usuarios pueden solicitar un nuevo email de verificación:

1. Acceder a `/verify-email`
2. Hacer clic en "Reenviar email de verificación"
3. El sistema respeta el throttling (máximo 6 por minuto)
4. Mensaje de confirmación al enviar exitosamente

## Recuperación de Contraseña

### ¿Cómo Funciona?

1. **Solicitud**: Usuario ingresa su email en "Olvidé mi contraseña"
2. **Token**: Sistema genera token único y lo almacena en `password_reset_tokens`
3. **Email**: Se envía enlace con el token al email del usuario
4. **Restablecimiento**: Usuario ingresa nueva contraseña con el token
5. **Actualización**: Contraseña se actualiza y el token se invalida

### Implementación Técnica

#### Rutas de Recuperación

Definidas en `routes/auth.php`:

```php
// Formulario "Olvidé mi contraseña"
Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

// Enviar enlace de recuperación
Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Formulario de nueva contraseña
Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

// Actualizar contraseña
Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');
```

#### Seguridad

- **Tokens únicos**: Cada solicitud genera un token nuevo
- **Expiración**: Los tokens expiran después de 60 minutos (configurable en `config/auth.php`)
- **Invalidación**: El token se elimina después de usarse
- **No revelación**: No se indica si el email existe en la base de datos (previene enumeración de usuarios)

#### Páginas Vue

- **`auth/ForgotPassword.vue`**: Formulario para solicitar recuperación
- **`auth/ResetPassword.vue`**: Formulario para establecer nueva contraseña

#### Base de Datos

Tabla `password_reset_tokens`:
```php
Schema::create('password_reset_tokens', function (Blueprint $table) {
    $table->string('email')->primary();
    $table->string('token');
    $table->timestamp('created_at')->nullable();
});
```

## Configuración

### Variables de Entorno

Configurar en `.env`:

```bash
# Configuración de Email
MAIL_MAILER=smtp                          # Driver: smtp, log, mailgun, etc.
MAIL_HOST=smtp.ejemplo.com                # Servidor SMTP
MAIL_PORT=587                             # Puerto SMTP (587 con TLS, 465 con SSL)
MAIL_USERNAME=tu_usuario                  # Usuario SMTP
MAIL_PASSWORD=tu_contraseña               # Contraseña SMTP
MAIL_ENCRYPTION=tls                       # Encriptación: tls o ssl
MAIL_FROM_ADDRESS="no-reply@dominio.com"  # Email remitente
MAIL_FROM_NAME="${APP_NAME}"              # Nombre remitente

# URL de la aplicación (necesaria para enlaces firmados)
APP_URL=https://tu-dominio.com
```

### Desarrollo Local

Para desarrollo, puedes usar:

#### Opción 1: Driver Log
```bash
MAIL_MAILER=log
```
Los emails se guardan en `storage/logs/laravel.log`

#### Opción 2: MailHog (Docker)
```bash
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

#### Opción 3: Mailtrap
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
```

### Producción

Opciones recomendadas para producción:

1. **SMTP de tu proveedor de hosting**
2. **SendGrid**: Servicio dedicado de emails transaccionales
3. **Mailgun**: Otra opción popular
4. **Amazon SES**: Económico para alto volumen

**Importante**: 
- Configura SPF y DKIM para mejorar la entregabilidad
- Usa dominio verificado para evitar que los emails vayan a spam
- Considera usar colas (`QUEUE_CONNECTION=database`) para envío asíncrono

### Configuración de Colas (Opcional pero Recomendado)

Para mejorar el rendimiento, envía emails de forma asíncrona:

1. Configurar en `.env`:
```bash
QUEUE_CONNECTION=database
```

2. Ejecutar worker de colas:
```bash
php artisan queue:work
```

O en producción con supervisor:
```ini
[program:sweet-nanny-worker]
command=php /ruta/a/tu/proyecto/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
```

## Pruebas

### Ejecutar Tests

```bash
# Todas las pruebas de autenticación
php artisan test --testsuite=Feature --filter=Auth

# Solo verificación de email
php artisan test --filter=EmailVerificationTest

# Solo recuperación de contraseña
php artisan test --filter=PasswordResetTest

# Middleware de verificación
php artisan test --filter=VerifiedMiddlewareTest
```

### Tests Implementados

#### Verificación de Email
- ✅ Email puede ser verificado con enlace válido
- ✅ Email no se verifica con hash inválido
- ✅ Notificación de verificación se envía correctamente
- ✅ Throttling respeta límite de 6 por minuto
- ✅ Usuarios no verificados son redirigidos a aviso
- ✅ Estado de verificación se comprueba correctamente

#### Recuperación de Contraseña
- ✅ Enlace de recuperación se puede solicitar
- ✅ Contraseña se puede restablecer con token válido
- ✅ Notificación de reset se envía al usuario

### QA Manual

#### Checklist de Verificación de Email

1. **Registro de Usuario**
   - [ ] Registrar nuevo usuario
   - [ ] Verificar que el email de verificación se envía (revisar logs si `MAIL_MAILER=log`)
   - [ ] Confirmar que el usuario NO puede acceder a `/users` (redirige a `/verify-email`)

2. **Verificación**
   - [ ] Hacer clic en el enlace del email
   - [ ] Verificar redirección al dashboard con parámetro `?verified=1`
   - [ ] Confirmar que ahora SÍ puede acceder a `/users`

3. **Reenvío**
   - [ ] Desde `/verify-email`, hacer clic en "Reenviar"
   - [ ] Confirmar mensaje de éxito
   - [ ] Intentar reenviar 7 veces seguidas
   - [ ] Verificar que la 7ª vez muestra error 429 (Too Many Requests)

4. **Enlace Expirado**
   - [ ] Intentar usar un enlace de verificación con fecha antigua (modificar URL)
   - [ ] Verificar que muestra error apropiado

#### Checklist de Recuperación de Contraseña

1. **Solicitud de Reset**
   - [ ] Ir a `/forgot-password`
   - [ ] Ingresar email de usuario existente
   - [ ] Verificar que se envía el email (revisar logs)
   - [ ] Confirmar mensaje: "A reset link will be sent if the account exists"

2. **Email Inexistente**
   - [ ] Ingresar email que no existe en la base de datos
   - [ ] Verificar que muestra el MISMO mensaje (no revela si existe)
   - [ ] Confirmar que NO se envía email real

3. **Restablecimiento**
   - [ ] Hacer clic en el enlace del email
   - [ ] Ingresar nueva contraseña (y confirmación)
   - [ ] Verificar redirección a `/login` con mensaje de éxito
   - [ ] Iniciar sesión con la nueva contraseña
   - [ ] Confirmar que la contraseña antigua ya no funciona

4. **Token Inválido**
   - [ ] Intentar usar el mismo enlace dos veces
   - [ ] Verificar error: "This password reset token is invalid"
   - [ ] Intentar con token modificado manualmente
   - [ ] Verificar el mismo error

5. **Token Expirado**
   - [ ] Simular token expirado (modificar `created_at` en DB)
   - [ ] Intentar usarlo
   - [ ] Verificar error de token inválido/expirado

## Preguntas Frecuentes

### ¿Los emails funcionan en tests?

Sí, pero se usa `Notification::fake()` para capturar las notificaciones sin enviarlas realmente.

### ¿Puedo personalizar los emails?

Sí, publica las notificaciones de Laravel:

```bash
php artisan vendor:publish --tag=laravel-notifications
```

O crea notificaciones personalizadas extendiendo las de Laravel.

### ¿Cómo cambio el tiempo de expiración de los tokens?

En `config/auth.php`:

```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60, // minutos
        'throttle' => 60, // segundos entre intentos
    ],
],
```

### ¿Puedo desactivar la verificación de email?

Sí, pero no es recomendado. Si lo necesitas:

1. Remover `implements MustVerifyEmail` del modelo `User`
2. Remover middleware `verified` de las rutas

### ¿Qué pasa si un usuario elimina el email antes de verificar?

El enlace seguirá funcionando mientras el usuario exista en la base de datos con ese email.

### ¿Los enlaces de verificación expiran?

Sí, los enlaces firmados expiran después de 60 minutos por defecto. Configurado en `VerifyEmailController`.

### ¿Puedo verificar usuarios manualmente desde la base de datos?

Sí, actualiza el campo `email_verified_at`:

```php
$user = User::find($id);
$user->markEmailAsVerified();
```

O directamente en la base de datos:
```sql
UPDATE users SET email_verified_at = NOW() WHERE id = 123;
```

## Soporte

Para problemas o preguntas:
- Revisar los logs: `storage/logs/laravel.log`
- Ejecutar tests: `php artisan test`
- Consultar la documentación oficial de Laravel sobre [Email Verification](https://laravel.com/docs/12.x/verification) y [Password Reset](https://laravel.com/docs/12.x/passwords)
