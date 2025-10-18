import { usePage } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import { useToast } from '@/components/ui/toast/use-toast';

/**
 * Composable to automatically handle flash messages from the backend
 * and display them as toasts using shadcn-style toast component.
 *
 * This should be called at the root level (in app.ts) to ensure it works
 * with both full page loads and partial Inertia reloads.
 */
export function useFlashMessages() {
    const { toast } = useToast();
    const page = usePage();

    // Track displayed messages to prevent duplicates
    const displayedMessages = new Set<string>();

    watchEffect(() => {
        const flash = (page.props.flash ?? {}) as Record<string, any>;

        // Process all flash message types
        for (const key of ['success', 'error', 'warning', 'info', 'status', 'message']) {
            const value = flash[key];
            if (!value) continue;

            // Generate unique key for deduplication
            const title = typeof value === 'string' ? value : value.title ?? key;
            const description = typeof value === 'string' ? undefined : value.description;
            const messageKey = `${key}:${title}:${description || ''}`;

            if (displayedMessages.has(messageKey)) continue;

            // Get styling for the message type
            const { cls, icon } = getStyleForType(key === 'message' ? 'success' : key);

            // Show toast
            toast({
                title,
                description,
                class: cls,
                icon,
                duration: 5500,
            });

            // Add to displayed set and schedule cleanup
            displayedMessages.add(messageKey);
            setTimeout(() => displayedMessages.delete(messageKey), 10000);
        }
    });
}

function getStyleForType(type: string): { cls: string; icon: string } {
    const base = 'border shadow-sm';
    const styleMap: Record<string, { cls: string; icon: string }> = {
        success: {
            cls: `${base} bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-50 dark:border-emerald-500/30`,
            icon: 'mdi:check-circle',
        },
        info: {
            cls: `${base} bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-500/20 dark:text-sky-50 dark:border-sky-500/30`,
            icon: 'mdi:information',
        },
        warning: {
            cls: `${base} bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-500/20 dark:text-amber-50 dark:border-amber-500/30`,
            icon: 'mdi:alert',
        },
        error: {
            cls: `${base} bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-500/20 dark:text-rose-50 dark:border-rose-500/30`,
            icon: 'mdi:close-circle',
        },
        status: {
            cls: `${base} bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-500/20 dark:text-sky-50 dark:border-sky-500/30`,
            icon: 'mdi:information',
        },
    };

    return styleMap[type] ?? styleMap.info;
}
