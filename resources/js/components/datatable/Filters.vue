<script setup>
import { ref, onMounted, inject, computed, watch } from "vue";
import { Button } from "../ui/button";
import Icon from "@/components/common/Icon.vue";

const props = defineProps({
    filtersName: {
        type: String,
        default: "",
    },
});

const filtersContainer = ref(null);

const hasFilters = ref(false);

const filters = inject(
    `${props.filtersName}_filters`,
    computed(() => false) // default if no injection found.
);

const clearFilters = inject(
    `clear_${props.filtersName}_filters`,
    () => {} // default if no injection found.
);

onMounted(() => {
    hasFilters.value = filtersContainer.value.children.length;
});

const emit = defineEmits(['onFilter']);

watch(
    filters,
    (newVal) => {
        emit('onFilter', { filters: filters.value });
    },
    { deep: true }
);
</script>

<template>
    <!-- Begin Filters -->
    <div class="flex flex-col gap-y-4 mt-5 py-5 border-t border-foreground/20">
        <!-- BEGIN: Filters slot -->
        <div class="flex flex-col xl:flex-row gap-y-3" ref="filtersContainer">
            <slot></slot>
        </div>

        <div class="flex gap-2 justify-self-end self-end">
            <!-- BEGING: Clear filters -->
            <Button
                @click="
                    () => {
                        clearFilters();
                        $emit('onFilter', { filters: {} });
                    }
                "
                variant="outline"
            >
                <Icon name="hugeicons:clean" :size="16" />

                Limpiar Filtros
            </Button>
            <!-- END: Clear filters -->
        </div>
    </div>
    <!-- END: Filters -->
</template>
