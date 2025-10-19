import { ref, type Ref } from 'vue';

export type ToastType = 'default' | 'success' | 'info' | 'warning' | 'error';

export interface Toast {
    id: string;
    title: string;
    description?: string;
    type?: ToastType;
    class?: string;
    icon?: string;
    duration?: number;
}

const toasts: Ref<Toast[]> = ref([]);

let toastIdCounter = 0;

export function useToast() {
    const toast = ({
        title,
        description,
        type = 'default',
        class: className,
        icon,
        duration = 5500,
    }: Omit<Toast, 'id'>) => {
        const id = `toast-${++toastIdCounter}-${Date.now()}`;
        const newToast: Toast = {
            id,
            title,
            description,
            type,
            class: className,
            icon,
            duration,
        };

        toasts.value.push(newToast);

        if (duration > 0) {
            setTimeout(() => {
                dismiss(id);
            }, duration);
        }

        return id;
    };

    const dismiss = (id: string) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const dismissAll = () => {
        toasts.value = [];
    };

    return {
        toast,
        toasts,
        dismiss,
        dismissAll,
    };
}
