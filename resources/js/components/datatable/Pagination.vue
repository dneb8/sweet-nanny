<template>
    <div class="col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center mt-4 gap-3">
        <nav class="w-full sm:w-auto sm:mr-auto">
            <ul class="flex items-center gap-4">
                <!-- BEGIN: First page -->
                <li class="max-[375px]:hidden">
                    <Button @click="pageOne" variant="ghost">
                        <Icon name="tabler:chevrons-left" :size="16" />
                    </Button>
                </li>
                <!-- END: First page -->

                <!-- BEGIN: Previous page -->
                <li>
                    <Button @click="previousPage" variant="ghost">
                        <Icon name="tabler:chevron-left" :size="16" />
                    </Button>
                </li>
                <!-- END: Previous page -->

                <!-- BEGIN: Page number -->
                <li v-for="page in displayablePages" :key="`page-${page}`">
                    <Button @click="gotoPage(page)" :variant="currentPage == page ? 'outline' : 'ghost'">
                        {{ page }}
                    </Button>
                </li>
                <!-- END: Page number -->

                <!-- BEGIN: Next page -->
                <li>
                    <Button @click="nextPage" variant="ghost">
                        <Icon name="tabler:chevron-right" :size="16" />
                    </Button>
                </li>
                <!-- END: Next page -->

                <!-- BEGIN: Last page -->
                <li class="max-[375px]:hidden">
                    <Button @click="lastPage" variant="ghost">
                        <Icon name="tabler:chevrons-right" :size="16" />
                    </Button>
                </li>
                <!-- END: Last page -->
            </ul>
        </nav>

        <p class="font-semibold">
            {{ totalRecordsText }}
        </p>

        <Select v-model="_paginationOffset">
            <SelectTrigger>
                <SelectValue placeholder="Selecciona una opción" />
            </SelectTrigger>

            <SelectContent>
                <SelectGroup>
                    <SelectLabel>
                        Registros por página
                    </SelectLabel>

                    <SelectItem
                        v-for="(option, index) in paginationOptions"
                        :key="index"
                        :value="option.value.toString()"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    </div>
</template>

<script>
import Icon from '@/components/common/Icon.vue';
import { Button } from '../ui/button';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '../ui/select';

export default {
    emits: ["onPageChange", "onOffsetChange"],
    props: {
        defaultCurrentPage: {
            type: Number,
            required: true,
        },
        pages: {
            type: Number,
            required: true,
        },
        defaultPaginationOffset: {
            type: Number,
            required: true,
        },
        totalRecords: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            currentPage: 1,
            paginationOffset: 1,
            validPaginationOffsets: {
                Todos: "all",
                0: this.defaultPaginationOffset,
                5: 5,
                10: 10,
                20: 20,
            },
            triggerEmitOnPageChange: true,
            paginationOptions:
                this.defaultPaginationOffset !== 5 && this.defaultPaginationOffset !== 10 && this.defaultPaginationOffset !== 20
                    ? [
                        {
                            label: 'Todos',
                            value: 'all',
                        },
                        {
                            label: 'Por defecto',
                            value: this.defaultPaginationOffset,
                        },
                        {
                            label: '5',
                            value: 5,
                        },
                        {
                            label: '10',
                            value: 10,
                        },
                        {
                            label: '20',
                            value: 20,
                        },
                    ] : [
                        {
                            label: 'Todos',
                            value: 'all',
                        },
                        {
                            label: '5',
                            value: 5,
                        },
                        {
                            label: '10',
                            value: 10,
                        },
                        {
                            label: '20',
                            value: 20,
                        },
                    ]
        };
    },
    methods: {
        gotoPage(page) {
            this.triggerEmitOnPageChange = true;
            this._page = page;
        },
        pageOne() {
            this.gotoPage(1);
        },
        lastPage() {
            this.gotoPage(this.pages);
        },
        previousPage() {
            if (Number(this.currentPage) <= 1) return;

            this.gotoPage(Number(this.currentPage) - 1);
        },
        nextPage() {
            if (Number(this.currentPage) >= this.pages) return;

            this.gotoPage(Number(this.currentPage) + 1);
        },
        determineMaxPagesPerSide() {
            return (window.innerWidth || document.body.clientWidth) >= 375
                ? 2
                : 1;
        },
    },
    computed: {
        _page: {
            // getter
            get() {
                return this.currentPage.toString();
            },
            // setter
            set(newValue) {
                this.currentPage = newValue.toString();
            },
        },
        _paginationOffset: {
            // getter
            get() {
                return this.paginationOffset.toString();
            },
            // setter
            set(newValue) {
                if (Number(newValue) < 1 && newValue !== "all") return;

                this.paginationOffset = newValue.toString();
            },
        },
        displayablePages() {
            let currentPage = Number(this.currentPage);
            let displayablePages = [currentPage];
            let sortAsc = (a, b) => (a < b ? -1 : a > b ? 1 : 0);

            const maxPagesPerSide = this.determineMaxPagesPerSide();
            for (let i = 1; i <= maxPagesPerSide; i++) {
                if (currentPage - i > 0) {
                    displayablePages.push(currentPage - i);
                }

                if (currentPage + i <= this.pages) {
                    displayablePages.push(currentPage + i);
                }
            }

            return displayablePages.sort(sortAsc);
        },
        totalRecordsText() {
            const lengthForPage = this.totalRecords.per_page * this.totalRecords.current_page < this.totalRecords.total ? this.totalRecords.per_page * this.totalRecords.current_page : this.totalRecords.total;
            return `Mostrando del ${this.totalRecords.per_page * (this.totalRecords.current_page - 1) + 1 } al ${ lengthForPage } de ${ this.totalRecords.total } resultados`
        },
    },
    watch: {
        defaultCurrentPage(page) {
            this.triggerEmitOnPageChange = false;
            this.currentPage = page;
        },
        _page(page) {
            if (this.triggerEmitOnPageChange) {
                this.$emit("onPageChange", { page });
            }

            this.triggerEmitOnPageChange = true;
        },
        _paginationOffset(offset) {
            /* this.validPaginationOffsets[offset] = offset; */
            this.$emit("onOffsetChange", { offset });
        },
    },
    mounted() {
        this.currentPage = this.defaultCurrentPage;
        this.paginationOffset = this.defaultPaginationOffset;
    },
    components: {
        Icon,
        Button,
        Select,
        SelectContent,
        SelectGroup,
        SelectItem,
        SelectLabel,
        SelectTrigger,
        SelectValue,
    },
};
</script>
