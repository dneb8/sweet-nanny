<script setup>
import { inject, ref } from "vue";
import { h, createVNode } from "vue";
import SelectableRowColumn from "./InputCheck.vue";
import { getNestedParamFromObject as resolveParamFromObject } from "./utils.js";
import Icon from "@/components/common/Icon.vue";

const props = defineProps({
    data: { type: Object, required: true },
    columns: { type: Array, required: true },
    fields: { type: Array, required: true },
    highlightSelectedRows: { type: Boolean, default: false },
    selectableRows: { type: Boolean, default: true },
});

const collapse = props.columns.some(column => column.props.collapseAlways === true);

const collapseShow = collapse ? props.data.map(d => false) : null;

const emit = defineEmits(["update:selectedRows"]);

const root = ref(null);

const { get: selectedRows, set: updateSelectedRows } = inject("selectedRows");

const resolveDataCollapse = (row) => {
    const data = props.columns.filter(column => column.props.collapseAlways === true).map(column => {
        let value = resolveParamFromObject(row, column.props.field);
        const onEmptyText = columnOnEmptyText(column);

        if (column.children?.hasOwnProperty("body")) {
            return h(column.children["body"], { record: row });
        }

        return h('p', {}, [
            h('b', {}, column.props.header + ": "),
            h('span', {}, !value && onEmptyText ? onEmptyText : value)
        ]);
    });

    return () => data;
};

function colapseHandle (rowIndex) {
    const buttonAdd = document.getElementById("collapse-button-add-" + rowIndex);
    const buttonSubtract = document.getElementById("collapse-button-subtract-" + rowIndex);
    const collapseInfo = document.getElementById("collapse-info-" + rowIndex);
    if(collapseShow[rowIndex]){
        collapseInfo.classList.add("hidden");
        buttonAdd.classList.remove("hidden");
        buttonSubtract.classList.add("hidden");
    }
    else{
        collapseInfo.classList.remove("hidden");
        buttonAdd.classList.add("hidden");
        buttonSubtract.classList.remove("hidden");
    }
    collapseShow[rowIndex] = !collapseShow[rowIndex];
};

const resolveColumnContent = (row, columnIndex, rowIndex, column) => {
    let value = resolveParamFromObject(row, props.fields[columnIndex]);
    const onEmptyText = columnOnEmptyText(column);

    if (!value && onEmptyText) {
        value = onEmptyText;
    }

    if (column.props?.avatar === true){
        return createVNode('img', { src: value, class: 'min-w-10 min-h-10 max-w-10 max-h-10 rounded-full object-cover' }, null);
    }
    else if (column.props?.clickable){
        return createVNode('a', { onClick: () => column.props.clickable(row), class: 'text-purple-500 dark:text-purple-400 cursor-pointer hover:underline' }, value);
    }

    return createVNode('span', { class: '' }, value);
};

const resolveColumnCustomContent = (column) => {
    return column.children["body"];
};

const columnHasCustomContent = (column) => {
    return column.children?.hasOwnProperty("body");
};

const isLastRow = (index) => props.data.length - 1 === index;

const columnOnEmptyText = (column) => {
    return column.props?.onEmptyText;
};

const handleSelectRow = (row, rowShouldBeSelected) => {
    if (rowShouldBeSelected) {
        updateSelectedRows([...selectedRows.value, row]);
    } else {
        updateSelectedRows(
            selectedRows.value.filter((selectedRow) => selectedRow !== row)
        );
    }
};
</script>

<template>
    <tbody class="bg-white/50 dark:bg-background/50 rounded-xl" ref="root">
        <tr v-show="!data.length" class="last:rounded-b-xl">
            <td :colspan="columns.length">
                <div class="flex flex-col place-items-center py-24 gap-2 rounded-xl">
                    <img src="images/empty-state-dark.svg" alt="No hay registros" class="size-64 opacity-90 hidden dark:block" />
                    <img src="images/empty-state.svg" alt="No hay registros" class="size-64 opacity-90 dark:hidden block" />

                    <span class="text-base">
                        No hay registros para mostrar.
                    </span>
                </div>
            </td>
        </tr>
        
        <template v-for="(row, rowIndex) in data">
            <tr
                tabindex="-1"
                class="bg-white-50 dark:bg-background"
                :class="{
                    '!bg-slate-200/60':
                        highlightSelectedRows &&
                        selectedRows.includes(rowIndex),
                }"
            >
                <template v-if="selectableRows">
                    <td>
                        <SelectableRowColumn
                            :value="selectedRows.includes(rowIndex)"
                            @selected="handleSelectRow(rowIndex, $event)"
                            title="Seleccionar fila"
                        />
                    </td>
                </template>

                <template v-if="collapse">
                    <td class="w-5">
                        <button class="mr-4">
                            <Icon name="tabler:chevron-right" :size="22" :id="`collapse-button-add-${ rowIndex }`" @click="colapseHandle(rowIndex)" />

                            <Icon name="tabler:chevron-down" :size="22" class="hidden" :id="`collapse-button-subtract-${ rowIndex }`" @click="colapseHandle(rowIndex)" />
                        </button>
                    </td>
                </template>

                <template v-for="(column, columnIndex) in columns">
                    <template v-if="column.props.collapseAlways !== true">
                        <td v-if="columnHasCustomContent(column)" class="border-y border-foreground/20 px-3 py-3 text-foreground/80">
                            <component
                                :is="resolveColumnCustomContent(column)"
                                :record="row"
                                :index="rowIndex"
                            />
                        </td>

                        <td v-else class="border-y border-foreground/20 px-3 py-3 text-foreground/80">
                            <component
                                :is="resolveColumnContent(
                                    row,
                                    columnIndex,
                                    rowIndex,
                                    column
                                )"
                            />
                        </td>
                    </template>
                </template>
            </tr>

            <tr :id="`collapse-info-${rowIndex}`" class="hidden">
                <td :colspan="columns.length" class="px-14 py-5">
                    <component :is="resolveDataCollapse(row)" />
                </td>
            </tr>
        </template>
    </tbody>
</template>
