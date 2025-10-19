# Sweet Nanny

Sistema de gestión de servicios de niñeras con autenticación completa.

## 🚀 Características

- **Autenticación completa** con verificación de email y recuperación de contraseña
- **Gestión de usuarios** con roles y permisos (usando Spatie Laravel Permission)
- **Panel de administración** para gestionar niñeras, tutores y cursos
- **Stack moderno**: Laravel 12 + Vue 3 + TypeScript + Inertia.js

## 📋 Requisitos

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL/MariaDB (o SQLite para desarrollo)

## ⚙️ Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/dneb8/sweet-nanny.git
   cd sweet-nanny
   ```

2. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias Node**
   ```bash
   npm install
   ```

4. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos**
   
   Editar `.env` con tus credenciales de base de datos:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sweetnanny
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   ```

7. **Opcional: Seeders**
   ```bash
   php artisan db:seed
   ```

## 🔐 Configuración de Email

Para que funcione la verificación de email y recuperación de contraseña, configura las variables de email en `.env`:

### Desarrollo (Log)
```env
MAIL_MAILER=log
```
Los emails se guardarán en `storage/logs/laravel.log`

### Desarrollo (Mailtrap)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@sweetnanny.test"
MAIL_FROM_NAME="${APP_NAME}"
```

### Producción
Ver [docs/auth.md](docs/auth.md) para opciones de producción (SendGrid, Mailgun, SES, etc.)

## 🧪 Desarrollo

### Iniciar servidor de desarrollo
```bash
composer dev
```

Esto iniciará:
- Laravel development server
- Vite dev server (hot reload)
- Queue worker

O ejecutar individualmente:
```bash
php artisan serve
npm run dev
```

### Ejecutar tests
```bash
composer test
# o
php artisan test
```

### Formatear código
```bash
# PHP
composer pint

# JavaScript/TypeScript/Vue
npm run format
```

## 📖 Documentación

- **[Autenticación](docs/auth.md)**: Verificación de email y recuperación de contraseña
  - Configuración de email
  - Cómo funciona la verificación
  - Cómo funciona el reset de contraseña
  - Tests y QA manual

## 🛡️ Seguridad

- Verificación de email obligatoria para rutas protegidas
- Tokens seguros para recuperación de contraseña
- Throttling (limitación de tasa) en rutas sensibles
- Protección CSRF
- Hashing seguro de contraseñas con bcrypt

## 📝 Licencia

Este proyecto es privado.
