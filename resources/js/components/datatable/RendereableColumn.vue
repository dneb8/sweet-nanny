<script setup>
import { inject } from "vue";

const props = defineProps({
    column: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <td
        v-if="columnHasCustomContent(columnIndex)"
        v-show="visibleColumns.includes(column.props.header)"
    >
        <component
            :is="resolveColumnCustomContent(columnIndex)"
            :record="row"
            :index="rowIndex"
        />
    </td>
    <td v-else v-show="visibleColumns.includes(column.props.header)">
        {{ resolveColumnContent(row, columnIndex, rowIndex) }}
    </td>
</template>
