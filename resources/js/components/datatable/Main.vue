<template>
    <div class="bg-background/50 dark:bg-background/50 p-4 rounded-lg space-y-4">
        <!-- BEGIN: Slot to non-render columns declaration -->
        <slot></slot>
        <!-- END: Slot to non-render columns declaration -->

    <!-- BEGIN: Box utilities -->
    <div>
    <div class="flex mb-2 gap-4">
        <!-- BEGIN: Searchbar -->
        <slot name="searchbar">
        <Searchbar
            v-if="useSearch"
            class="max-[578px]:w-full"
            @on-search="search"
            :search-term-name="searchTermName"
        />
        </slot>
        <!-- END: Searchbar -->

        <div
        class="flex gap-2 ml-auto items-center"
        >
        <slot name="before-actions"></slot>

        <!-- BEGIN: Columnas (el botón está dentro del componente) -->
        <SelectVisibleColumns
            v-if="canToggleColumnsVisibility && (responsiveCards ? screens[responsiveCards] < windowWidth : true)"
            class="max-[460px]:w-full"
            :togglableColumns="togglableColumns"
        />
        <!-- END: Columnas -->

        <!-- BEGIN: Filtros (Popover con el mismo botón como trigger) -->
        <template v-if="useFilters">
            <Popover :open="openFilters" @update:open="val => (openFilters = val)">
            <PopoverTrigger as-child>
                <Button variant="outline" class="flex items-center">
                <Icon name="mage:filter" :size="16" class="mr-0 sm:mr-2" />
                <span class="hidden sm:inline">Filtros</span>
                </Button>
            </PopoverTrigger>

            <PopoverContent
                align="end"
                side="bottom"
                :side-offset="8"
                class=" p-4 space-y-4"
            >
                <div class="flex items-center justify-between">
                <h4 class="font-medium leading-none">Filtros</h4>
                </div>

                <Filters
                :filters-name="resourcePropName"
                @onFilter="(payload) => { filter(payload); openFilters = false }"
                >
                <slot name="filters"></slot>
                </Filters>

                <div class="flex justify-end gap-2 pt-2">
                <Button variant="outline" class="h-8" @click="openFilters = false">
                    Cancelar
                </Button>
                <Button class="h-8" @click="openFilters = false">
                    Aplicar
                </Button>
                </div>
            </PopoverContent>
            </Popover>
        </template>
        <!-- END: Filtros (Popover) -->

        <slot name="after-actions"></slot>
        </div>
    </div>
    </div>
    <!-- END: Box utilities -->


        <Transition name="fade">
            <span v-show="selectedRows.length">
                {{ selectedRows.length }} registros seleccionados.
            </span>
        </Transition>

        <!-- BEGIN: Table content -->
        <div class="relative overflow-x-auto overflow-y-hidden soft-scrollbar rounded-lg">
            <slot name="before-table"></slot>

            <!-- BEGIN: Loader -->
            <Transition name="fade">
                <div v-show="isLoading" class="absolute rounded-lg inset-0 flex justify-center items-center bg-background/60 z-[51]">
                    <Icon name="line-md:loading-loop" :size="20" />
                </div>
            </Transition>
            <!-- END: Loader -->

            <div class="py-5" v-if="customDesign">
                <div v-if="!records.length" class="flex flex-col place-items-center py-24 gap-2">
                    <img src="images/empty-state-dark.svg" alt="No hay registros" class="size-64 opacity-90 hidden dark:block" />
                    <img src="images/empty-state.svg" alt="No hay registros" class="size-64 opacity-90 dark:hidden block" />

                    <span class="text-base">
                        No hay registros para mostrar.
                    </span>
                </div>

                <slot name="custom-design" :slotProps="records"></slot>
            </div>

            <table
                v-else-if="responsiveCards ? screens[responsiveCards] < windowWidth : true"
                class="w-full my-4 overflow-hidden scrollbar-hidden"
                :class="[
                    compact ? 'border border-slate-200' : '',
                ]"
            >
                <TableHeader
                    :headers="headers"
                    :total-records-displayed="records.length"
                    :sortableColumns="sortableColumns"
                    :selectableRows="selectableRows"
                    @sort="onSorting"
                    :collapse="columns.some(column => column.props.collapseAlways === true)"
                />

                <TableBody
                    :data="records"
                    :columns="columns"
                    :highlightSelectedRows="highlightSelectedRows"
                    :fields="fields"
                    :selectableRows="selectableRows"
                />
            </table>

            <template v-else>
                <div v-if="!records.length" class="flex flex-col place-items-center py-24 gap-2">
                    <img src="images/empty-state-dark.svg" alt="No hay registros" class="size-64 opacity-90 hidden dark:block" />
                    <img src="images/empty-state.svg" alt="No hay registros" class="size-64 opacity-90 dark:hidden block" />

                    <span class="text-base">
                        No hay registros para mostrar.
                    </span>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-x-4 w-full">
                    <div v-for="(dato, index) in records">
                        <div :id="index" class="w-full h-full my-2">
                            <slot :slotProps="dato" name="responsive-card"></slot>
                        </div>
                    </div>
                </div>
            </template>

            <slot name="after-table"></slot>
        </div>
        <!-- END: Table content -->

        <!-- BEGIN: Pagination -->
        <Pagination
            v-if="usePagination"
            :pages="pages"
            :defaultCurrentPage="defaultCurrentPage"
            :defaultPaginationOffset="defaultPaginationOffset"
            :totalRecords="totalRecords"
            @onPageChange="changePage"
            @onOffsetChange="changePaginationOffset"
            @change.stop
        />
        <!-- END: Pagination -->
    </div>
</template>

<script>
import { router } from "@inertiajs/vue3";
import { computed } from "vue";
import { enter, leave } from "@/layouts/index.js";
import Searchbar from "./Searchbar.vue";
import TableBody from "./TableBody.vue";
import TableHeader from "./TableHeader.vue";
import Pagination from "./Pagination.vue";
import SelectVisibleColumns from "./SelectVisibleColumns.vue";
import Filters from "./Filters.vue";
import { LucideFilterX, Filter } from "./Icons";
import { Button } from "../ui/button";
import Icon from "@/components/common/Icon.vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from "../ui/sheet";

export default {
    props: {
        refKey: { type: String, default: "" },
        resource: { type: Object, required: true },
        resourcePropName: { type: String, required: false },
        keyRowsBy: { type: String, default: "id" },
        compact: { type: Boolean, default: false },
        useFilters: { type: Boolean, default: false },
        useSearch: { type: Boolean, default: true },
        usePagination: { type: Boolean, default: true },
        selectableRows: { type: Boolean, default: false },
        selectedRows: { type: Array, default: [] },
        canToggleColumnsVisibility: { type: Boolean, default: false },
        visibleColumns: { type: Array, default: [] },
        highlightSelectedRows: { type: Boolean, default: false },
        customDesign: { type: Boolean, default: false },
        onBeforeFetch: {
            type: Function,
            async default(params) {
                return params;
            },
        },
        exportRoute: { type: String, default: "" },
        additionalExportOptions: { type: Object },
        responsiveCards: { type: String, default: "" },
    },
    data() {
        return {
            isLoading: false,
            openFilters: false,
            dataParams: {},
            enterAnimation: enter,
            leaveAnimation: leave,
            showExportModal: false,
            windowWidth: window.innerWidth,
            screens: {
                sm: 480,
                md: 768,
                lg: 976,
                xl: 1440,
            },
        };
    },
    provide() {
        return {
            refKey: computed(() => this.refKey),
            keyRowsBy: this.keyRowsBy,
            columnBooleanProp: this.columnBooleanProp,
            headers: computed(() => this.headers),
            columns: computed(() => this.columns),
            rawColumns: computed(() => this._columns),
            records: computed(() => this.records),
            visibleColumns: {
                get: computed(() => this.visibleColumns),
                set: this.updateVisibleColumns,
            },
            selectedRows: {
                get: computed(() => this.selectedRows),
                set: this.updateSelectedRows,
            },
        };
    },
    mounted() {
        this.dataParams.per_page = this.defaultPaginationOffset;
        window.addEventListener('resize', this.handleResize);
    },
    methods: {
        handleResize() {
            this.windowWidth = window.innerWidth;
        },
        updateSelectedRows(newSelectedRows) {
            this.$emit("update:selectedRows", newSelectedRows);
        },
        updateVisibleColumns(newVisibleColumns) {
            this.$emit("update:visibleColumns", newVisibleColumns);
        },
        columnBooleanProp(column, propertyName) {
            return column.type.props[propertyName].type === Boolean &&
                column.props[propertyName] === ""
                ? true
                : column.props[propertyName];
        },
        changePage({ page }) {
            this.dataParams[this.pageName] = page;

            this.refetch();
        },
        changePaginationOffset({ offset }) {
            if (this.defaultPaginationOffset == offset) return;
            if(offset == "all") this.dataParams.page = 1;

            this.dataParams.per_page = offset;

            this.refetch();
        },
        search(search) {
            this.dataParams[this.searchTermName] = search;
            this.dataParams[this.pageName] = 1;

            this.refetch();
        },
        filter({ filters }) {
            this.dataParams.filters = filters;
            this.dataParams[this.pageName] = 1;

            this.refetch();
        },
        sort(sortField, sortDirection) {
            this.dataParams.sortBy = sortField;
            this.dataParams.sortDirection = sortDirection;

            this.refetch();
        },
        onSorting({ column, direction }) {
            this.sort(column, direction);
        },
        async refetch() {
            const params = await this.onBeforeFetch(this.formattedParams);

            router.get(
                window.location.origin +
                    window.location.pathname +
                    "?" +
                    this.handleQuerystring(),
                params,
                {
                    only: [this.resourcePropName],
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onStart: () => (this.isLoading = true),
                    onFinish: () => (this.isLoading = false),
                }
            );
        },
        handleQuerystring() {
            let querystring = new URLSearchParams(window.location.search);

            if (this.dataParams[this.pageName] == 1) {
                querystring.delete(this.pageName);
            }

            if (!this.dataParams[this.searchTermName]) {
                querystring.delete(this.searchTermName);
            }

            if (!this.dataParams.sortBy) {
                querystring.delete("sortBy");
            }

            if (!this.dataParams.sortDirection) {
                querystring.delete("sortDirection");
            }

            this.removeFiltersFromQuerystring(querystring);

            return querystring.toString();
        },
        removeFiltersFromQuerystring(querystring) {
            let filterParams = [];

            querystring.forEach((value, param) => {
                let isFilter = this.getQuerystringFilterName(param);

                if (isFilter) {
                    filterParams.push(param);
                }
            });

            filterParams.forEach((param) => querystring.delete(param));
        },
        getFiltersFromQuerystring(querystring) {
            let filters = {};

            querystring.forEach((value, param) => {
                let filterName = this.getQuerystringFilterName(param);

                if (filterName) {
                    let filterIsArray = this.querystringFilterIsArray(param);

                    if (filterIsArray) {
                        if (filters[filterName]) {
                            filters[filterName].push(value);
                        } else {
                            filters[filterName] = [value];
                        }
                    } else {
                        filters[filterName] = value;
                    }
                }
            });

            return filters;
        },
        addParamToQuerystring(querystring, value, replace = true) {
            if (querystring.has("")) {
            }
        },
        querystringFilterIsArray(param) {
            let pattern = /^filters\[[A-Za-z0-9]+]\[\]$/i;

            return param.match(pattern);
        },
        getQuerystringFilterName(param) {
            let pattern = /(filters)\[(\w*)\](\[\])?/g;

            let match = pattern.exec(param);

            return match ? match[2] : false;
        },
    },
    computed: {
        /**
         * Filtra las columnas recibidas para
         * uso de forma interna.
         */
        _columns() {
            const isColumn = (content) => content.type.name === "Column";

            return this.$slots.default().filter(isColumn);
        },
        totalRecords(){
            return {
                total: this.totalDataRecords,
                per_page: this.defaultPaginationOffset,
                current_page: this.defaultCurrentPage,
            }
        },
        headers() {
            const headers = this._columns.filter(column => column.props.collapseAlways !== true).map((column) => {
                return {
                    name: column.props.header,
                    class: column.props.headerClass,
                };
            });

            if (this.canToggleColumnsVisibility) {
                return headers.filter((column) =>
                    this.visibleColumns.includes(column.name)
                );
            } else {
                return headers;
            }
        },
        columns() {
            if (this.canToggleColumnsVisibility) {
                return this._columns.filter((column) =>
                    this.visibleColumns.includes(column.props.header)
                );
            } else {
                return this._columns;
            }
        },
        fields() {
            const fields = this.columns.map((column) => column.props.field);

            return fields;
        },
        sortableColumns() {
            const sortableColumns = this.columns.filter((col) => {
                return this.columnBooleanProp(col, "sortable");
            });

            return sortableColumns.map((col) => col.props.header);
        },
        togglableColumns() {
            const togglableColumns = this._columns.filter((col) => {
                return this.columnBooleanProp(col, "togglable") && !this.columnBooleanProp(col, "collapseAlways");
            });

            return togglableColumns.map((col) => col.props.header);
        },
        searchTermName() {
            return this.refKey ? `${this.refKey}-searchTerm` : "searchTerm";
        },
        pageName() {
            return this.refKey ? `${this.refKey}-page` : "page";
        },
        records() {
            return this.resource.data;
        },
        defaultCurrentPage() {
            return this.resource.current_page;
        },
        defaultPaginationOffset() {
            return this.resource.per_page;
        },
        totalDataRecords() {
            return this.resource.total;
        },
        pages() {
            let pages = Math.ceil(
                this.totalDataRecords / this.defaultPaginationOffset
            );
            return pages ? pages : 1;
        },
        formattedParams() {
            let params = { ...this.dataParams };

            if (this.dataParams[this.pageName] == 1) {
                delete params[this.pageName];
            }

            if (!this.dataParams[this.searchTermName]) {
                delete params[this.searchTermName];
            }

            if (!this.dataParams.sortBy) {
                delete params.sortBy;
            }

            if (!this.dataParams.sortDirection) {
                delete params.sortDirection;
            }

            return params;
        },
    },
    components: {
        TableBody,
        TableHeader,
        Pagination,
        SelectVisibleColumns,
        Searchbar,
        Filters,
        LucideFilterX,
        Filter,
        Button,
        Icon,
        Sheet,
        SheetClose,
        SheetContent,
        SheetDescription,
        SheetFooter,
        SheetHeader,
        SheetTitle,
        SheetTrigger,
        Popover,
        PopoverContent,
        PopoverTrigger,
        
    },
};
</script>
