# Users DataTable Documentation

## Overview

The Users DataTable implements a server-side data table with pagination, sorting, filtering, and search capabilities. It supports both table and card views with URL state synchronization for shareable links and browser navigation.

## Features

- **Server-side Processing**: All filtering, sorting, and pagination is handled on the server for optimal performance
- **Real-time Search**: Debounced search with 300ms delay and automatic request cancellation
- **Multiple Filters**: Filter by role, email verification status
- **Sortable Columns**: Click column headers to sort by name, email, role, email verification, or creation date
- **View Toggle**: Switch between table and card views
- **URL Synchronization**: All state is reflected in the URL for shareable links and browser history support
- **Pagination**: Configurable per-page limits with full pagination controls

## URL Parameters

The DataTable supports the following URL query parameters:

| Parameter | Type | Description | Example Values |
|-----------|------|-------------|----------------|
| `search` | string | Search users by name, surnames, or email | `?search=juan` |
| `sort` | string | Field to sort by | `name`, `email`, `created_at`, `email_verified_at`, `role` |
| `dir` | string | Sort direction | `asc`, `desc` |
| `page` | integer | Current page number | `1`, `2`, `3`, etc. |
| `per_page` | integer | Items per page (max 100) | `15`, `25`, `50`, `100` |
| `view` | string | Display mode | `table`, `cards` |
| `filters[role]` | string | Filter by user role | `admin`, `tutor`, `nanny` |
| `filters[verified]` | string | Filter by email verification status | `verified`, `unverified` |

## Example URLs

### Basic Search
```
/users?search=maria
```

### Filtered by Role
```
/users?filters[role]=tutor
```

### Sorted and Paginated
```
/users?sort=name&dir=asc&page=2&per_page=25
```

### Combined Filters
```
/users?search=juan&filters[role]=tutor&filters[verified]=verified&sort=created_at&dir=desc
```

### Card View with Filters
```
/users?view=cards&filters[role]=nanny&filters[verified]=verified
```

### Complete Example
```
/users?search=ana&role=tutor&status=active&verified=1&sort=name&dir=asc&page=3&per_page=25&view=table
```

## Backend Implementation

### UserService::indexFetch()

The `indexFetch()` method in `UserService` handles all server-side processing:

```php
public function indexFetch(): LengthAwarePaginator
{
    $request = request();
    $query = User::query()->with(['roles', 'tutor', 'nanny.qualities']);

    // Search
    if ($search = $request->get('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('surnames', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Filters
    if ($filters = $request->get('filters')) {
        // Role filter
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Email verified filter
        if (!empty($filters['verified'])) {
            if ($filters['verified'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($filters['verified'] === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }
    }

    // Sorting (with whitelist)
    $sortField = $request->get('sort', 'created_at');
    $sortDirection = $request->get('dir', 'desc');
    
    $allowedSortFields = ['name', 'email', 'created_at', 'email_verified_at'];
    if (!in_array($sortField, $allowedSortFields)) {
        $sortField = 'created_at';
    }

    $query->orderBy($sortField, $sortDirection);

    // Pagination
    $perPage = min((int) $request->get('per_page', 15), 100);
    return $query->paginate($perPage)->appends($request->query());
}
```

### Security Considerations

1. **SQL Injection Prevention**: Sort fields are validated against a whitelist
2. **Input Sanitization**: All user input is sanitized before database queries
3. **Pagination Limits**: Per-page values are capped at 100 to prevent resource exhaustion
4. **N+1 Query Prevention**: Relationships are eager-loaded using `with()`

## Frontend Implementation

### UserDataTable Component

The main component located at `resources/js/components/data/UserDataTable.vue` provides:

- Search input with debouncing
- Filter panel with role and verification status options
- Table/card view toggle
- Sortable column headers
- Pagination controls
- Loading states

### useDataTable Composable

The `useDataTable` composable (`resources/js/composables/useDataTable.ts`) manages:

- URL state synchronization
- Debounced API requests (300ms)
- Request cancellation for pending requests
- State management for search, filters, sorting, and pagination

```typescript
const { state, loading, setSearch, setFilter, clearFilters, setSort, setPage, setView } = useDataTable('users.index', {
    view: 'table',
    per_page: 15,
});
```

## Usage

### In a Vue Component

```vue
<script setup lang="ts">
import UserDataTable from '@/components/data/UserDataTable.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { User } from '@/types/User';

const props = defineProps<{
    users: FetcherResponse<User>;
}>();
</script>

<template>
    <UserDataTable :users="users" />
</template>
```

### Accessing from URLs

Users can bookmark or share filtered/sorted views:

```
https://example.com/users?search=juan&filters[role]=tutor&sort=name&dir=asc
```

When navigating back, forward, or reloading the page, the state is restored from the URL.

## Testing

Comprehensive tests are available in `tests/Feature/UserDataTableTest.php`:

- Search functionality
- Role filtering
- Email verification filtering
- Sorting (ascending/descending)
- Pagination
- Combined filters
- Security (SQL injection prevention)
- Input sanitization

Run tests with:

```bash
php artisan test tests/Feature/UserDataTableTest.php
```

## Performance Considerations

1. **Debouncing**: 300ms delay prevents excessive API calls during typing
2. **Request Cancellation**: Previous requests are aborted when new ones are made
3. **Eager Loading**: Relationships are loaded efficiently to prevent N+1 queries
4. **Pagination**: Limits result sets to manageable sizes
5. **Index Optimization**: Ensure database indexes on `name`, `email`, `created_at`, and `email_verified_at` columns

## Accessibility

The DataTable component includes:

- Proper ARIA labels for buttons and controls
- Keyboard navigation support
- Focus indicators
- Screen reader friendly table structure
- Semantic HTML elements

## Extending the DataTable

To add additional filters:

1. Update `UserService::indexFetch()` to handle the new filter
2. Add filter UI in `UserDataTable.vue`
3. Update `useDataTable` composable if needed
4. Add tests for the new filter
5. Document the new parameter in this file

Example of adding a date range filter:

```php
// In UserService::indexFetch()
if (!empty($filters['created_from'])) {
    $query->whereDate('created_at', '>=', $filters['created_from']);
}
if (!empty($filters['created_to'])) {
    $query->whereDate('created_at', '<=', $filters['created_to']);
}
```

## Troubleshooting

### Filters not working
- Check browser console for JavaScript errors
- Verify URL parameters are being passed correctly
- Check backend logs for query errors

### Slow performance
- Verify database indexes exist
- Check for N+1 queries using Laravel Debugbar
- Consider reducing per_page limit
- Optimize complex filter queries

### State not persisting in URL
- Check that `preserveState: true` and `replace: true` are set in Inertia requests
- Verify router.get is being called with correct parameters
- Check browser console for navigation errors
