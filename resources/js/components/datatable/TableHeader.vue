<script setup>
import { computed, inject, ref, onMounted } from "vue";
import { useQuerystringStore } from "@/stores/useQuerystring";
import Icon from "@/components/common/Icon.vue";

const props = defineProps({
  headers: { type: Array, required: true },
  totalRecordsDisplayed: { type: Number, required: true },
  sortableColumns: { type: Array, required: true },
  selectableRows: { type: Boolean, required: true },
  collapse: { type: Boolean, required: true },
});

const emit = defineEmits(["update:selectedRows", "sort"]);

const columns = inject("columns");
const refKey = inject("refKey");
const { get: selectedRows, set: updateSelectedRows } = inject("selectedRows");

const sortedColumn = ref(null);       // string | null
const sortDirection = ref("");        // 'asc' | 'desc' | ''

const querystring = useQuerystringStore();

const columnIsSortable = (header) => props.sortableColumns.includes(header.name);

const allRowsAreSelected = computed(
  () => selectedRows.value.length === props.totalRecordsDisplayed
);

const getColumnByHeader = (header) =>
  columns.value.find((col) => col.props.header === header);

const calculateNextSortDirection = (header) => {
  const field = getColumnByHeader(header)?.props?.field;

  if (field !== sortedColumn.value || !sortDirection.value) return "asc";
  if (sortDirection.value === "asc") return "desc";
  if (sortDirection.value === "desc") return "";
  return "";
};

const sortedColumnQuerystringName = computed(() =>
  refKey.value ? `${refKey.value}-sortBy` : "sortBy"
);

const sortDirectionQuerystringName = computed(() =>
  refKey.value ? `${refKey.value}-sortDirection` : "sortDirection"
);

const resolveSortIcon = (dir) => {
  const map = {
    asc: "tabler:sort-ascending",
    desc: "tabler:sort-descending",
    "": "tabler:arrows-sort",
  };
  return map[dir] ?? "tabler:arrows-sort";
};

const currentSortIcon = (headerName) => {
  const field = getColumnByHeader(headerName)?.props?.field;
  const dir = field === sortedColumn.value ? sortDirection.value : "";
  return resolveSortIcon(dir);
};

const handleSort = (header) => {
  const field = getColumnByHeader(header.name)?.props?.field;
  if (!field) return; // sin field no hay sort
  if (!columnIsSortable(header)) return;

  const next = calculateNextSortDirection(header.name);

  emit("sort", { column: field, direction: next });
  sortedColumn.value = field;
  sortDirection.value = next;
};

const handleSelectAllRows = (rowsShouldBeSelected) => {
  if (rowsShouldBeSelected) {
    updateSelectedRows([...Array(props.totalRecordsDisplayed).keys()]);
  } else {
    updateSelectedRows([]);
  }
};

onMounted(() => {
  sortedColumn.value = querystring.get(sortedColumnQuerystringName.value);
  sortDirection.value = querystring.get(sortDirectionQuerystringName.value) ?? "";
});
</script>

<template>
  <thead>
    <tr class="first:rounded-t-xl">
      <template v-if="selectableRows && totalRecordsDisplayed > 0">
        <th class="first:rounded-l-md">
          <s
            :value="allRowsAreSelected"
            @selected="handleSelectAllRows"
            title="Seleccionar todos"
          />
        </th>
      </template>

      <template v-if="collapse">
        <th></th>
      </template>

      <template v-for="header in headers" :key="`header-${header.name}`">
        <th class="first:rounded-tl-md last:rounded-tr-md px-3 py-4 text-sm text-foreground whitespace-nowrap font-bold">
          <div class="flex items-center gap-1" :class="header.class">
            {{ header.name }}

            <template v-if="columnIsSortable(header)">
              <!-- <button
                type="button"
                class="ml-1 inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                :disabled="!getColumnByHeader(header.name)?.props?.field"
                :aria-label="`Ordenar por ${header.name}`"
                @click="handleSort(header)"
              >
                <Icon :name="currentSortIcon(header.name)" :size="16" />
              </button> -->
            </template>
          </div>
        </th>
      </template>
    </tr>
  </thead>
</template>
