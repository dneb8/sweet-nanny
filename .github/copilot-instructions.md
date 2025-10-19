# Copilot Instructions for Sweet Nanny

This repository contains a **Laravel 12 + Vue 3 + TypeScript + Inertia.js** full-stack web application for managing nanny services.

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: MySQL (with Docker support)
- **Additional Packages**:
  - Inertia.js Laravel adapter
  - Spatie Laravel Permission for role management
  - Ziggy for Laravel route generation in JavaScript

### Frontend
- **Framework**: Vue 3 with Composition API
- **Language**: TypeScript
- **UI Libraries**:
  - Reka UI components
  - Tailwind CSS 4.x
  - Lucide icons
- **State Management**: Pinia
- **Form Validation**: Vee-Validate with Zod schemas
- **Build Tool**: Vite

### Testing
- **PHP Testing**: Pest (preferred over PHPUnit)
- **Test Structure**: Feature and Unit tests in `tests/` directory

## Coding Standards & Conventions

### PHP Code
- **Style**: Follow Laravel conventions
- **Formatter**: Laravel Pint (run `composer pint` or `./vendor/bin/pint`)
- **Indentation**: 4 spaces
- **Line Endings**: LF (Unix-style)
- **Named Arguments**: Use named arguments in constructors and method calls where it improves clarity (e.g., `paginate($offset, pageName: $pageName)`)

### TypeScript/Vue Code
- **Formatter**: Prettier with the following settings:
  - Single quotes for strings
  - Semicolons required
  - 4 spaces indentation (except 2 spaces for YAML files)
  - Print width: 150 characters
  - Tailwind class sorting enabled
- **Linter**: ESLint with Vue and TypeScript support
- **Component Style**: 
  - Use Vue 3 Composition API with `<script setup lang="ts">`
  - Multi-word component names rule is disabled
  - Type annotations preferred (`@typescript-eslint/no-explicit-any` is disabled but use sparingly)
- **Import Alias**: Use `@/` for imports from `resources/js/`
- **Type Definitions**: Place in `resources/js/types/` directory with `.d.ts` extension

### File Organization
- **PHP**: 
  - Controllers in `app/Http/Controllers/`
  - Models in `app/Models/`
  - Services in `app/Services/`
  - Custom classes in `app/Classes/`
- **Vue**:
  - Pages in `resources/js/Pages/`
  - Components in `resources/js/components/`
  - UI components (from shadcn/vue) in `resources/js/components/ui/`
  - Layouts in `resources/js/layouts/`
  - Composables in `resources/js/composables/`
  - Type definitions in `resources/js/types/`

## Build, Test & Development Commands

### Development
```bash
# Start full development stack (Laravel server + queue + Vite)
composer dev

# Vue/TypeScript development only
npm run dev

# SSR development mode
composer dev:ssr
```

### Building
```bash
# Build frontend assets
npm run build

# Build with SSR support
npm run build:ssr
```

### Testing
```bash
# Run PHP tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

### Linting & Formatting
```bash
# Format frontend code
npm run format

# Check frontend formatting
npm run format:check

# Lint and fix JavaScript/TypeScript/Vue
npm run lint

# Format PHP code
composer pint
# or
./vendor/bin/pint
```

## Architecture Patterns

### Inertia.js Integration
- Pages are Vue components that receive props from Laravel controllers
- Use `@inertiajs/vue3` for navigation and form handling
- Page props include `auth.user` for authenticated user data
- Use `route()` helper (from Ziggy) for generating Laravel routes in Vue components

### Data Fetching Pattern
- Custom `Fetcher` class in `app/Classes/Fetcher/` for consistent query building and pagination
- Supports filtering, sorting, and flexible pagination (including "all" option)
- Use `Fetcher::for($query)` to create instances

### UI Components
- Prefer using existing components from `resources/js/components/ui/` (based on shadcn/vue)
- These components are ignored by ESLint and should not be modified directly
- Custom form components: `Input`, `Label`, `Button`, etc.

### Type Safety
- Define TypeScript interfaces for all data models in `resources/js/types/`
- Use Zod schemas with Vee-Validate for form validation
- Import types from `@/types` in Vue components

## Important Notes

- **Docker**: The project includes Docker configuration with `docker-compose.yml`
- **Environment**: Copy `.env.example` to `.env` for local development
- **Database**: SQLite for development, MySQL in Docker
- **Ignored Directories**: 
  - ESLint ignores: `vendor`, `node_modules`, `public`, `bootstrap/ssr`, `resources/js/components/ui/*`
  - Prettier ignores: `vendor/**`, `node_modules/**`, etc.
- **Language**: Application appears to be in Spanish (UI text in components)

## When Adding New Features

1. **Backend**: 
   - Create controller methods following RESTful conventions
   - Use Inertia render for page responses: `Inertia::render('PageName', [...props])`
   - Define routes in `routes/web.php` or `routes/api.php`
   
2. **Frontend**:
   - Create page components in `resources/js/Pages/`
   - Use TypeScript interfaces for props
   - Follow existing component patterns for consistency
   - Use Tailwind utility classes for styling
   
3. **Testing**:
   - Write Pest tests for new features
   - Place feature tests in `tests/Feature/`
   - Place unit tests in `tests/Unit/`

## Security & Permissions

- Use Spatie Laravel Permission package for role/permission management
- Authentication scaffolding is already in place
- Email verification is supported (`mustVerifyEmail` prop)
