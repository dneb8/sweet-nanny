import { useToast as useShadcnToast } from '@/components/ui/toast/use-toast';

/**
 * Legacy composable for backward compatibility.
 * Now uses shadcn-style toast instead of vue-sonner.
 */
export function useToast() {
    const { toast } = useShadcnToast();

    const success = (message: string, description?: string) => {
        const base = 'border shadow-sm';
        toast({
            title: message,
            description,
            class: `${base} bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-50 dark:border-emerald-500/30`,
            icon: 'mdi:check-circle',
            duration: 5000,
        });
    };

    const error = (message: string, description?: string) => {
        const base = 'border shadow-sm';
        toast({
            title: message,
            description,
            class: `${base} bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-500/20 dark:text-rose-50 dark:border-rose-500/30`,
            icon: 'mdi:close-circle',
            duration: 6000,
        });
    };

    const warning = (message: string, description?: string) => {
        const base = 'border shadow-sm';
        toast({
            title: message,
            description,
            class: `${base} bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-500/20 dark:text-amber-50 dark:border-amber-500/30`,
            icon: 'mdi:alert',
            duration: 5000,
        });
    };

    const info = (message: string, description?: string) => {
        const base = 'border shadow-sm';
        toast({
            title: message,
            description,
            class: `${base} bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-500/20 dark:text-sky-50 dark:border-sky-500/30`,
            icon: 'mdi:information',
            duration: 5000,
        });
    };

    return {
        success,
        error,
        warning,
        info,
        toast, // Expose raw toast for advanced usage
    };
}
