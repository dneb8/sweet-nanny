# DataTable Quick Reference

## 🚀 Quick Start

### Basic Usage

```vue
<script setup lang="ts">
import DataTable, { type DataTableColumn } from '@/components/datatable/DataTable.vue';

const columns: DataTableColumn[] = [
  { id: 'name', header: 'Name', sortable: true },
  { id: 'email', header: 'Email', sortable: true },
];

const data = [
  { name: 'John', email: 'john@example.com' },
  { name: 'Jane', email: 'jane@example.com' },
];
</script>

<template>
  <DataTable :columns="columns" :data="data" />
</template>
```

### With Backend Pagination

```vue
<DataTable
  :columns="columns"
  :data="users.data"
  :links="{ prev: users.prev_page_url, next: users.next_page_url }"
  :page="users.current_page"
  :per-page="users.per_page"
  :total="users.total"
  :last-page="users.last_page"
  @search="handleSearch"
  @sort:change="handleSort"
  @goto="handleGoto"
/>
```

## 📝 Column Definition

```typescript
interface DataTableColumn<T> {
  id: string;                // Unique identifier
  header: string;            // Column header text
  accessorKey?: keyof T;     // Property key to access
  filterable?: boolean;      // Enable column filtering
  filterType?: 'text' | 'select' | 'date' | 'number'; // Filter type
  filterOptions?: Array<{ label: string; value: string | number | boolean }>; // For select filters
  cell?: (row: T) => any;    // Custom cell renderer
  sortable?: boolean;        // Enable sorting
  headerClass?: string;      // Tailwind classes for <th>
  cellClass?: string;        // Tailwind classes for <td>
}
```

### Examples

```typescript
// Simple column with accessor
{ id: 'name', header: 'Name', accessorKey: 'name', sortable: true }

// Column with custom cell renderer
{ id: 'status', header: 'Status', cell: (row) => row.active ? 'Active' : 'Inactive' }

// Column with custom classes
{ id: 'price', header: 'Price', accessorKey: 'price', cellClass: 'text-right font-bold' }
```

## 🎨 Slots

### Cell Customization
```vue
<template #cell-name="{ row, value }">
  <strong>{{ value }}</strong>
</template>
```

### Actions Column
```vue
<template #actions="{ row }">
  <Button @click="edit(row)">Edit</Button>
  <Button @click="delete(row)">Delete</Button>
</template>
```

### Mobile Card View
```vue
<DataTable :card-slot="true" ...>
  <template #card="{ row }">
    <Card>
      <CardHeader>{{ row.name }}</CardHeader>
      <CardContent>{{ row.email }}</CardContent>
    </Card>
  </template>
</DataTable>
```

### Empty State
```vue
<template #empty>
  <div class="text-center py-8">
    <Icon icon="mdi:inbox" class="w-16 h-16 mb-2" />
    <p>No data found</p>
  </div>
</template>
```

### Additional Controls
```vue
<template #controls>
  <Button @click="exportData">Export</Button>
</template>
```

## 🔧 Events

### @search
Triggered when search button is clicked.
```typescript
function handleSearch(value: string) {
  // Fetch data with search query
}
```

### @sort:change
Triggered when sortable column header is clicked.
```typescript
function handleSort({ id, direction }: { id: string, direction: 'asc' | 'desc' | null }) {
  // Fetch data with sort parameters
}
```

### @goto
Triggered when pagination button is clicked.
```typescript
function handleGoto(url: string) {
  // Navigate to URL
  router.get(url);
}
```

### @filters:change
Triggered when filters are applied.
```typescript
function handleFiltersChange(filters: Record<string, string | number | boolean | null>) {
  // Fetch data with filters
  router.get(route('index'), { filters });
}
```

### @view:change
Triggered when view mode is changed manually.
```typescript
function handleViewChange(view: 'table' | 'cards' | 'auto') {
  // Save preference or update state
  currentView.value = view;
}
```

## 📱 Responsive

The component has three view modes controlled by the `viewMode` prop:

**Auto (default):**
- Automatically switches at 768px breakpoint
- Desktop (≥ 768px): Table view
- Mobile (< 768px): Card view (if card slot provided)

**Table:**
- Forces table view regardless of screen size

**Cards:**
- Forces card view regardless of screen size

The view can be changed manually using the toggle button (3 options: Table, Cards, Auto).

## 🎯 Common Patterns

### Inertia.js Integration

```typescript
// Get initial state from URL
const urlParams = new URLSearchParams(window.location.search);
const initialSearch = urlParams.get('search') || '';
const initialSort = urlParams.get('sort') || null;
const initialDir = urlParams.get('dir') as 'asc' | 'desc' | null;

// Preserve filters when updating
function getCurrentParams() {
  const params = new URLSearchParams(window.location.search);
  const result: Record<string, string> = {};
  params.forEach((value, key) => { result[key] = value; });
  return result;
}

function handleSearch(value: string) {
  const params = getCurrentParams();
  router.get(route('index'), {
    ...params,
    search: value || undefined,
    page: undefined, // Reset to page 1
  }, { preserveState: true, preserveScroll: true, replace: true });
}

function handleSort({ id, direction }) {
  const params = getCurrentParams();
  router.get(route('index'), {
    ...params,
    sort: direction ? id : undefined,
    dir: direction || undefined,
    page: undefined,
  }, { preserveState: true, preserveScroll: true, replace: true });
}

function handleFiltersChange(filters: Record<string, string | number | boolean | null>) {
  const params = getCurrentParams();
  router.get(route('index'), {
    ...params,
    ...filters,
    page: undefined,
  }, { preserveState: true, preserveScroll: true, replace: true });
}
```

### Column Filters

```typescript
const columns: DataTableColumn<User>[] = [
  {
    id: 'name',
    header: 'Name',
    sortable: true,
    filterable: true,
    filterType: 'text',
  },
  {
    id: 'status',
    header: 'Status',
    filterable: true,
    filterType: 'select',
    filterOptions: [
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ],
  },
];
```

### View Mode Control

```vue
<script setup lang="ts">
const viewMode = ref<'auto' | 'table' | 'cards'>('auto');
</script>

<template>
  <DataTable
    :view-mode="viewMode"
    @view:change="(mode) => viewMode = mode"
    ...
  />
</template>
```

### Badge in Cell

```vue
<template #cell-role="{ row }">
  <Badge :variant="getBadgeVariant(row.role)">
    {{ row.role }}
  </Badge>
</template>
```

### Icon in Cell

```vue
<template #cell-name="{ row }">
  <div class="flex items-center gap-2">
    <span>{{ row.name }}</span>
    <Icon v-if="row.verified" icon="mdi:check-circle" class="text-green-500" />
  </div>
</template>
```

### Date Formatting

```vue
<template #cell-created="{ row }">
  {{ new Date(row.created_at).toLocaleDateString() }}
</template>
```

## 💡 Tips

1. **Always provide unique `id`** for each column
2. **Use `sortable: true`** only for backend-sortable columns
3. **Provide `card` slot** for better mobile UX
4. **Use `searchQuery`, `sortBy`, `sortDir`** props to sync UI with URL state
5. **Preserve filters** when updating (see Inertia.js pattern above)
6. **Use slots** for custom cell rendering instead of complex cell functions
7. **Test responsive** behavior at different breakpoints

## ⚠️ Common Pitfalls

❌ **Don't**: Use reactive sorting/filtering locally
```vue
<!-- WRONG - defeats purpose of backend control -->
const sortedData = computed(() => data.sort(...))
```

✅ **Do**: Emit events and let parent fetch from backend
```vue
<!-- CORRECT -->
@sort:change="handleSort"
```

❌ **Don't**: Forget to handle null direction in sort
```typescript
// WRONG
sort: id,
dir: direction, // Could be null!
```

✅ **Do**: Only send sort params when direction exists
```typescript
// CORRECT
sort: direction ? id : undefined,
dir: direction || undefined,
```

❌ **Don't**: Lose filters when paginating/sorting
```vue
<!-- WRONG -->
router.get(route('index'), { sort: id })
```

✅ **Do**: Preserve existing params
```typescript
// CORRECT
const params = getCurrentParams();
router.get(route('index'), { ...params, sort: id });
```

## 🔗 Related Components

- **Input**: Search field
- **Button**: Search trigger, column menu
- **DropdownMenu**: Column visibility toggle
- **Pagination**: Navigation controls
- **Table**: Desktop view
- **Card**: Mobile view (user-provided via slot)

## 📖 Full Documentation

See `resources/js/components/datatable/README.md` for complete documentation with all props, events, and advanced examples.
