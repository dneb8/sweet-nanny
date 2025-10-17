import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';

export interface DataTableFilters {
    [key: string]: string | null | undefined;
}

export interface DataTableState {
    search: string;
    filters: DataTableFilters;
    sort: string | null;
    dir: 'asc' | 'desc';
    page: number;
    per_page: number;
    view: 'table' | 'cards';
}

export function useDataTable(
    routeName: string,
    defaults: Partial<DataTableState> = {},
    debounceMs: number = 300
) {
    // Current request abort controller
    let abortController: AbortController | null = null;

    // Initialize state from URL params or defaults
    const urlParams = new URLSearchParams(window.location.search);
    
    const state = ref<DataTableState>({
        search: urlParams.get('search') || defaults.search || '',
        filters: defaults.filters || {},
        sort: urlParams.get('sort') || defaults.sort || null,
        dir: (urlParams.get('dir') as 'asc' | 'desc') || defaults.dir || 'desc',
        page: parseInt(urlParams.get('page') || '1') || defaults.page || 1,
        per_page: parseInt(urlParams.get('per_page') || '15') || defaults.per_page || 15,
        view: (urlParams.get('view') as 'table' | 'cards') || defaults.view || 'table',
    });

    // Parse filters from URL
    const filtersFromUrl: DataTableFilters = {};
    urlParams.forEach((value, key) => {
        if (key.startsWith('filters[') && key.endsWith(']')) {
            const filterKey = key.slice(8, -1);
            filtersFromUrl[filterKey] = value;
        }
    });
    if (Object.keys(filtersFromUrl).length > 0) {
        state.value.filters = filtersFromUrl;
    }

    const loading = ref(false);

    // Build query params for API request
    function buildQueryParams(): Record<string, any> {
        const params: Record<string, any> = {
            search: state.value.search || undefined,
            sort: state.value.sort || undefined,
            dir: state.value.dir || undefined,
            page: state.value.page > 1 ? state.value.page : undefined,
            per_page: state.value.per_page !== 15 ? state.value.per_page : undefined,
            view: state.value.view !== 'table' ? state.value.view : undefined,
        };

        // Add filters
        if (state.value.filters) {
            Object.entries(state.value.filters).forEach(([key, value]) => {
                if (value) {
                    params[`filters[${key}]`] = value;
                }
            });
        }

        // Remove undefined values
        return Object.fromEntries(
            Object.entries(params).filter(([, v]) => v !== undefined)
        );
    }

    // Fetch data from server
    const fetchData = useDebounceFn(() => {
        // Cancel previous request if it exists
        if (abortController) {
            abortController.abort();
        }

        // Create new abort controller
        abortController = new AbortController();

        loading.value = true;

        const queryParams = buildQueryParams();

        router.get(
            route(routeName),
            queryParams,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['users'],
                onStart: () => {
                    loading.value = true;
                },
                onFinish: () => {
                    loading.value = false;
                },
                onCancel: () => {
                    loading.value = false;
                },
            }
        );
    }, debounceMs);

    // Watch for state changes and trigger fetch
    watch(
        () => [state.value.search, state.value.filters, state.value.sort, state.value.dir, state.value.page, state.value.per_page, state.value.view],
        () => {
            fetchData();
        },
        { deep: true }
    );

    // Update search
    function setSearch(value: string) {
        state.value.search = value;
        state.value.page = 1; // Reset to first page
    }

    // Update filters
    function setFilter(key: string, value: string | null) {
        state.value.filters = {
            ...state.value.filters,
            [key]: value,
        };
        state.value.page = 1; // Reset to first page
    }

    // Clear all filters
    function clearFilters() {
        state.value.filters = {};
        state.value.search = '';
        state.value.page = 1;
    }

    // Update sort
    function setSort(field: string) {
        if (state.value.sort === field) {
            // Toggle direction
            state.value.dir = state.value.dir === 'asc' ? 'desc' : 'asc';
        } else {
            state.value.sort = field;
            state.value.dir = 'asc';
        }
        state.value.page = 1; // Reset to first page
    }

    // Update page
    function setPage(page: number) {
        state.value.page = page;
    }

    // Update per page
    function setPerPage(perPage: number) {
        state.value.per_page = perPage;
        state.value.page = 1; // Reset to first page
    }

    // Update view
    function setView(view: 'table' | 'cards') {
        state.value.view = view;
    }

    return {
        state,
        loading,
        setSearch,
        setFilter,
        clearFilters,
        setSort,
        setPage,
        setPerPage,
        setView,
    };
}
