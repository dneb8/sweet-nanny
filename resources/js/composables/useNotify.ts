import { h } from 'vue';
import { toast } from 'vue-sonner';
import { Icon } from '@iconify/vue';

// Variantes + íconos por defecto
const ICONS = {
    success: 'mdi:check-circle',
    info: 'mdi:information-outline',
    warning: 'mdi:alert',
    error: 'mdi:close-circle',
};

// Esquemas de color por variante (Tailwind classes)
const VARIANTS = {
    success: 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-100 dark:border-emerald-800',
    info: 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/20 dark:text-sky-100 dark:border-sky-800',
    warning: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:border-amber-800',
    error: 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-900/20 dark:text-rose-100 dark:border-rose-800',
};

// Colores de íconos por variante
const ICON_COLORS = {
    success: 'text-emerald-600 dark:text-emerald-400',
    info: 'text-sky-600 dark:text-sky-400',
    warning: 'text-amber-600 dark:text-amber-400',
    error: 'text-rose-600 dark:text-rose-400',
};

export type NotifyVariant = 'success' | 'info' | 'warning' | 'error';

export interface NotifyOptions {
    variant: NotifyVariant;
    title: string;
    description?: string;
    icon?: string;
    duration?: number;
}

/**
 * Composable para mostrar notificaciones toast con soporte de Iconify,
 * variantes de color y unificación de flash messages + notificaciones en tiempo real.
 */
export function useNotify() {
    /**
     * Muestra una notificación toast personalizada
     */
    const notify = (options: NotifyOptions) => {
        const {
            variant,
            title,
            description,
            icon = ICONS[variant],
            duration = 5000,
        } = options;

        const variantClass = VARIANTS[variant];
        const iconColor = ICON_COLORS[variant];

        // Renderiza el toast personalizado con Iconify icon
        toast.custom(
            () =>
                h(
                    'div',
                    {
                        class: `flex items-start gap-3 p-4 rounded-lg border shadow-lg ${variantClass} min-w-[320px] max-w-[420px]`,
                        role: 'alert',
                    },
                    [
                        // Icon
                        h(Icon, {
                            icon,
                            class: `w-5 h-5 flex-shrink-0 mt-0.5 ${iconColor}`,
                        }),
                        // Content
                        h('div', { class: 'flex-1 min-w-0' }, [
                            // Title
                            h('div', { class: 'font-semibold text-sm mb-0.5' }, title),
                            // Description (optional)
                            description &&
                                h(
                                    'div',
                                    {
                                        class: 'text-xs opacity-90 mt-1',
                                    },
                                    description
                                ),
                        ]),
                    ]
                ),
            { duration }
        );
    };

    // Helpers para cada variante
    const notifySuccess = (title: string, description?: string, icon?: string, duration?: number) => {
        notify({ variant: 'success', title, description, icon, duration });
    };

    const notifyInfo = (title: string, description?: string, icon?: string, duration?: number) => {
        notify({ variant: 'info', title, description, icon, duration });
    };

    const notifyWarning = (title: string, description?: string, icon?: string, duration?: number) => {
        notify({ variant: 'warning', title, description, icon, duration });
    };

    const notifyError = (title: string, description?: string, icon?: string, duration?: number) => {
        notify({ variant: 'error', title, description, icon, duration });
    };

    return {
        notify,
        notifySuccess,
        notifyInfo,
        notifyWarning,
        notifyError,
    };
}
