<script setup lang="ts">
import { TransitionGroup } from 'vue';
import Toast from './Toast.vue';
import { useToast } from './use-toast';

const { toasts, dismiss } = useToast();
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end gap-2 p-4 sm:p-6">
        <TransitionGroup
            name="toast"
            tag="div"
            class="flex w-full max-w-sm flex-col gap-2"
        >
            <Toast
                v-for="toast in toasts"
                :key="toast.id"
                :toast="toast"
                :on-dismiss="dismiss"
            />
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
