<script setup>
import { inject, computed } from "vue";
import Icon from "@/components/common/Icon.vue";
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "../ui/dropdown-menu";
import { Button } from "../ui/button";

const props = defineProps({
    togglableColumns: {
        type: Array,
        required: true,
    },
});

const { get: visibleColumns, set: updateVisibleColumns } = inject("visibleColumns");

const columns = inject("rawColumns");

const columnBooleanProp = inject("columnBooleanProp");

const notTogglableColumns = computed(() =>
    columns.value
        .filter((col) => !columnBooleanProp(col, "togglable") || columnBooleanProp(col, "collapseAlways"))
        .map((col) => col.props.header)
);

const handleUpdateVisibleColumns = (column, columnVisible) => {
    if (columnVisible) {
        const newVisibleColumns = [...visibleColumns.value, column];
        updateVisibleColumns(newVisibleColumns);
    } else {
        const newVisibleColumns = visibleColumns.value.filter(
            (col) => col !== column
        );
        updateVisibleColumns(newVisibleColumns);
    }
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline">
                Columnas
                <Icon name="tabler:chevron-down" :size="16" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-56">
            <DropdownMenuLabel>
                Columnas visibles
            </DropdownMenuLabel>

            <DropdownMenuSeparator />

            <DropdownMenuCheckboxItem
                v-for="column in [...notTogglableColumns, ...togglableColumns]"
                :key="`check-column-${column}`"
                :modelValue="visibleColumns.includes(column)"
                @update:modelValue="handleUpdateVisibleColumns(column, $event)"
            >
                {{ column }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
