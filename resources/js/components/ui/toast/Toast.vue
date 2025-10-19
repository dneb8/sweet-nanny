<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { X } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Toast } from './use-toast';

const props = defineProps<{
    toast: Toast;
    onDismiss: (id: string) => void;
}>();

const toastClass = computed(() => {
    return props.toast.class || '';
});
</script>

<template>
    <div
        :class="[
            'pointer-events-auto relative w-full overflow-hidden rounded-lg border shadow-lg transition-all',
            'bg-background text-foreground',
            toastClass,
        ]"
        role="alert"
        aria-live="polite"
        aria-atomic="true"
    >
        <div class="flex items-start gap-3 p-4">
            <Icon
                v-if="toast.icon"
                :icon="toast.icon"
                class="mt-0.5 h-5 w-5 shrink-0 opacity-80"
            />
            <div class="flex-1 space-y-1">
                <div class="text-sm font-semibold leading-none tracking-tight">
                    {{ toast.title }}
                </div>
                <div v-if="toast.description" class="text-sm opacity-90">
                    {{ toast.description }}
                </div>
            </div>
            <button
                type="button"
                class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md opacity-70 transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-offset-2"
                @click="onDismiss(toast.id)"
            >
                <X class="h-4 w-4" />
                <span class="sr-only">Close</span>
            </button>
        </div>
    </div>
</template>
