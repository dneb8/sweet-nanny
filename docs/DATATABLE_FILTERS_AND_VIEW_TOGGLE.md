# DataTable: Column Filters & Manual View Toggle

## Overview

This document describes the column filters and manual view toggle features added to the DataTable component based on feedback.

## 1. Column Filters

### Purpose
Allow users to filter data by specific columns with backend-controlled filtering (no client-side data mutation).

### Column Configuration

Add filter properties to column definitions:

```typescript
interface DataTableColumn<T> {
  // ... existing properties
  filterable?: boolean;
  filterType?: 'text' | 'select' | 'date' | 'number';
  filterOptions?: Array<{ label: string; value: string | number | boolean }>;
}
```

### Example

```typescript
const columns: DataTableColumn<User>[] = [
  {
    id: 'name',
    header: 'Nombre',
    sortable: true,
    filterable: true,
    filterType: 'text',
  },
  {
    id: 'role',
    header: 'Rol',
    filterable: true,
    filterType: 'select',
    filterOptions: [
      { label: 'Administrador', value: 'admin' },
      { label: 'Usuario', value: 'user' },
      { label: 'Niñera', value: 'nanny' },
    ],
  },
  {
    id: 'age',
    header: 'Edad',
    filterable: true,
    filterType: 'number',
  },
];
```

### Props & Events

**Props:**
- `columnFilters?: Record<string, string | number | boolean | null>` - Initial filter state

**Events:**
- `@filters:change` - Emitted when filters are applied
  ```typescript
  (filters: Record<string, string | number | boolean | null>) => void
  ```

### UI Behavior

1. **Filter Button**: Appears automatically when any column has `filterable: true`
   - Icon: `mdi:filter-variant`
   - Highlighted when filters are active
   
2. **Filter Panel**: Opens in a popover
   - Shows one filter control per filterable column
   - Filter types:
     - **Text**: Standard input field
     - **Number**: Number input field
     - **Select**: Dropdown with predefined options
     - **Date**: (Reserved for future implementation)
   
3. **Actions**:
   - **Clear**: Resets all filters and emits empty object
   - **Apply**: Emits current filter state
   - **Enter Key**: Same as Apply (for text/number inputs)

4. **No Local Filtering**: 
   - Filters do NOT modify the displayed data
   - Parent component must refetch from backend with filter parameters

### Implementation Example

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3';

// Get initial filters from URL
const urlParams = new URLSearchParams(window.location.search);
const initialFilters: Record<string, string> = {};
urlParams.forEach((value, key) => {
  if (key.startsWith('filter_')) {
    const filterKey = key.replace('filter_', '');
    initialFilters[filterKey] = value;
  }
});

function handleFiltersChange(filters: Record<string, string | number | boolean | null>) {
  const params = getCurrentParams();
  
  // Convert filters to URL format
  const filterParams: Record<string, any> = {};
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== '') {
      filterParams[`filter_${key}`] = value;
    }
  });
  
  router.get(route('users.index'), {
    ...params,
    ...filterParams,
    page: undefined, // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}
</script>

<template>
  <DataTable
    :columns="columns"
    :data="users.data"
    :column-filters="initialFilters"
    @filters:change="handleFiltersChange"
    ...
  />
</template>
```

### Backend Integration (Laravel Example)

```php
public function index(Request $request)
{
    $query = User::query();
    
    // Apply filters
    if ($name = $request->get('filter_name')) {
        $query->where('name', 'like', "%{$name}%");
    }
    
    if ($role = $request->get('filter_role')) {
        $query->whereHas('roles', fn($q) => $q->where('name', $role));
    }
    
    if ($age = $request->get('filter_age')) {
        $query->where('age', '>=', $age);
    }
    
    $users = $query->paginate(15);
    
    return Inertia::render('User/Index', [
        'users' => $users,
    ]);
}
```

---

## 2. Manual View Toggle

### Purpose
Allow users to manually control whether data is displayed as a table or cards, overriding the automatic responsive behavior.

### Props & Events

**Props:**
- `viewMode?: 'auto' | 'table' | 'cards'` (default: 'auto')
  - `'auto'`: Responsive behavior (table ≥768px, cards <768px)
  - `'table'`: Always show table view
  - `'cards'`: Always show cards view

**Events:**
- `@view:change` - Emitted when view mode changes
  ```typescript
  (view: 'table' | 'cards' | 'auto') => void
  ```

**Slots:**
- `#view-toggle` - Replace default toggle with custom implementation

### UI Behavior

1. **Default Toggle**: Three-button toggle group
   - 📊 Table icon (`mdi:table`)
   - 🔲 Cards icon (`mdi:view-grid`)
   - 🔄 Auto icon (`mdi:auto-fix`)
   
2. **View Logic**:
   - **'table'**: Forces table view regardless of screen size
   - **'cards'**: Forces cards view regardless of screen size
   - **'auto'**: Uses responsive breakpoint (768px)

3. **Persistence**: 
   - View preference can be saved to localStorage
   - Or persisted via URL parameter
   - Parent component controls the state

### Implementation Example

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue';

// Load preference from localStorage
const viewMode = ref<'auto' | 'table' | 'cards'>('auto');

onMounted(() => {
  const saved = localStorage.getItem('dataTableViewMode');
  if (saved && ['auto', 'table', 'cards'].includes(saved)) {
    viewMode.value = saved as 'auto' | 'table' | 'cards';
  }
});

function handleViewChange(view: 'table' | 'cards' | 'auto') {
  viewMode.value = view;
  localStorage.setItem('dataTableViewMode', view);
}
</script>

<template>
  <DataTable
    :view-mode="viewMode"
    @view:change="handleViewChange"
    :card-slot="true"
    ...
  >
    <!-- Card template required for cards view -->
    <template #card="{ row }">
      <UserCard :user="row" />
    </template>
  </DataTable>
</template>
```

### Custom Toggle Example

```vue
<template>
  <DataTable :view-mode="viewMode" @view:change="handleViewChange" ...>
    <!-- Custom toggle implementation -->
    <template #view-toggle>
      <Select :model-value="viewMode" @update:model-value="handleViewChange">
        <SelectTrigger class="w-[140px]">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="auto">Automático</SelectItem>
          <SelectItem value="table">Tabla</SelectItem>
          <SelectItem value="cards">Tarjetas</SelectItem>
        </SelectContent>
      </Select>
    </template>
  </DataTable>
</template>
```

---

## Key Design Decisions

### 1. Backend-First Approach
Both features emit events rather than modifying data locally:
- Filters emit `@filters:change` → parent refetches
- View mode emits `@view:change` → parent saves preference
- Maintains consistency with search and sort behavior

### 2. Apply/Enter Pattern
Filters follow the same UX pattern as search:
- Changes are staged locally
- No automatic filtering while typing
- Must press Enter or click "Apply"
- Prevents excessive backend requests

### 3. View Mode State
View mode is controlled by parent:
- Component receives `viewMode` prop
- Component emits `@view:change` event
- Parent decides how to persist (localStorage, URL, etc.)
- Allows for user preferences across sessions

### 4. Backward Compatibility
All changes are optional:
- Existing implementations work without modification
- New features opt-in via column properties and props
- Default behavior unchanged (auto responsive, no filters)

---

## Testing Checklist

- [ ] Filter button appears when columns have `filterable: true`
- [ ] Filter button is highlighted when filters are active
- [ ] Text filters accept input and emit on Apply/Enter
- [ ] Number filters accept numeric input
- [ ] Select filters show all options
- [ ] Clear button resets all filters
- [ ] Filters don't mutate local data
- [ ] `@filters:change` event includes all active filters
- [ ] View toggle shows 3 options (table/cards/auto)
- [ ] Table mode forces table view on mobile
- [ ] Cards mode forces cards view on desktop
- [ ] Auto mode uses responsive breakpoint
- [ ] `@view:change` event fires when toggle changes
- [ ] Custom toggle via `#view-toggle` slot works
- [ ] Documentation examples are accurate

---

## Browser Support

- Modern browsers with ES6+ support
- Responsive design tested on:
  - Desktop: Chrome, Firefox, Safari, Edge
  - Mobile: iOS Safari, Chrome Mobile
  - Breakpoint: 768px (Tailwind `md`)

---

## Future Enhancements

Potential additions for future versions:

1. **Date Range Filters**: Implement date picker for `filterType: 'date'`
2. **Multi-Select Filters**: Allow selecting multiple values for select filters
3. **Filter Presets**: Save and load common filter combinations
4. **Advanced Filters**: Support for operators (contains, equals, greater than, etc.)
5. **Filter Count Badge**: Show number of active filters on button
6. **View Mode Shortcuts**: Keyboard shortcuts for switching views
7. **Column Sorting in Filter Panel**: Reorder filterable columns
8. **Filter History**: Remember recent filter values

---

## Migration Guide

For existing DataTable implementations:

### No Changes Required
If you don't want filters or view toggle, nothing needs to change. Your current implementation will continue to work as before.

### Adding Filters

1. Add `filterable: true` to column definitions
2. Optionally specify `filterType` (defaults to 'text')
3. For select filters, provide `filterOptions`
4. Handle `@filters:change` event in parent component
5. Pass initial state via `columnFilters` prop

### Adding View Toggle

1. Add `viewMode` state to parent component
2. Pass `:view-mode="viewMode"` to DataTable
3. Handle `@view:change` event to update state
4. Optionally persist to localStorage or URL

### Example Migration

**Before:**
```vue
<DataTable :columns="columns" :data="data" />
```

**After (with filters):**
```vue
<DataTable
  :columns="columnsWithFilters"
  :data="data"
  :column-filters="activeFilters"
  @filters:change="handleFilters"
/>
```

**After (with view toggle):**
```vue
<DataTable
  :columns="columns"
  :data="data"
  :view-mode="viewMode"
  @view:change="(v) => viewMode = v"
/>
```
