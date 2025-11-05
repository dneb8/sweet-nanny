<script setup lang="ts">
/**
 * CtaModal - Unified Call-to-Action Modal Component
 *
 * A flexible modal component for confirmations, warnings, and deletions.
 * Maintains the same UI/UX as the original DeleteModal but supports multiple types.
 *
 * @example
 * // Delete action (red, trash icon)
 * <CtaModal
 *   :show="showModal"
 *   type="delete"
 *   message="¿Estás seguro de eliminar este elemento?"
 *   :onConfirm="handleDelete"
 *   @update:show="showModal = $event"
 * />
 *
 * @example
 * // Warning action (amber, alert icon)
 * <CtaModal
 *   :show="showModal"
 *   type="warning"
 *   title="Advertencia importante"
 *   message="Esta acción puede tener consecuencias."
 *   confirmText="Continuar de todos modos"
 *   :onConfirm="handleWarning"
 *   @update:show="showModal = $event"
 * />
 *
 * @example
 * // Confirm action (blue, check icon)
 * <CtaModal
 *   :show="showModal"
 *   type="confirm"
 *   message="¿Confirmas esta acción?"
 *   :onConfirm="handleConfirm"
 *   @update:show="showModal = $event"
 * />
 */
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import { Trash2, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';

type CtaType = 'delete' | 'warning' | 'confirm';

const props = withDefaults(
    defineProps<{
        show: boolean;
        type?: CtaType;
        title?: string;
        message: string;
        confirmText?: string;
        cancelText?: string;
        onConfirm: () => void;
        onCancel?: () => void;
        loading?: boolean;
    }>(),
    {
        type: 'delete',
        loading: false,
    }
);

const emit = defineEmits(['update:show']);

const isOpen = ref(props.show);

watch(
    () => props.show,
    (val) => {
        isOpen.value = val;
    }
);

const close = () => {
    emit('update:show', false);
    props.onCancel?.();
};

const confirmAction = () => {
    props.onConfirm();
    close();
};

// Dynamic configurations based on type
const config = computed(() => {
    switch (props.type) {
        case 'delete':
            return {
                title: props.title || 'Confirmar eliminación',
                icon: Trash2,
                iconColor: 'text-rose-600',
                glowColor: 'bg-rose-600',
                buttonVariant: 'destructive' as const,
                buttonText: props.confirmText || 'Eliminar',
            };
        case 'warning':
            return {
                title: props.title || 'Advertencia',
                icon: AlertTriangle,
                iconColor: 'text-amber-500',
                glowColor: 'bg-amber-500',
                buttonVariant: 'default' as const,
                buttonText: props.confirmText || 'Continuar',
            };
        case 'confirm':
            return {
                title: props.title || 'Confirmar acción',
                icon: CheckCircle2,
                iconColor: 'text-blue-600',
                glowColor: 'bg-blue-600',
                buttonVariant: 'default' as const,
                buttonText: props.confirmText || 'Confirmar',
            };
    }
});

const cancelButtonText = computed(() => props.cancelText || 'Cancelar');
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('update:show', $event)">
        <DialogContent class="flex flex-col items-center text-center gap-4">
            <!-- Icono con luz circular difusa -->
            <div class="relative flex items-center justify-center">
                <!-- Luz difusa detrás -->
                <span :class="['absolute size-12 rounded-full opacity-50 blur-xl animate-pulse-glow', config.glowColor]"></span>
                <!-- Icono encima -->
                <component :is="config.icon" :class="['size-12 relative z-10', config.iconColor]" />
            </div>

            <!-- Título -->
            <DialogHeader class="mt-2">
                <DialogTitle class="text-lg font-semibold">{{ config.title }}</DialogTitle>
            </DialogHeader>

            <!-- Mensaje -->
            <p class="text-sm text-muted-foreground">{{ message }}</p>

            <!-- Botones alineados siempre -->
            <DialogFooter class="flex flex-row justify-center gap-3 w-full mt-4">
                <Button variant="ghost" @click="close" :disabled="loading" class="flex-1">{{ cancelButtonText }}</Button>
                <Button :variant="config.buttonVariant" @click="confirmAction" :disabled="loading" class="flex-1">
                    {{ config.buttonText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
@keyframes glow {
    0%,
    100% {
        opacity: 0.4;
        transform: scale(0.9);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.1);
    }
}

.animate-pulse-glow {
    animation: glow 1.2s infinite ease-in-out;
}
</style>
