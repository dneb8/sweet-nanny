import { usePage } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import { toast } from 'vue-sonner';

/**
 * Composable to automatically handle flash messages from the backend
 * and display them as toasts using vue-sonner.
 *
 * This should be called at the root level (in app.ts) to ensure it works
 * with both full page loads and partial Inertia reloads.
 */
export function useFlashMessages() {
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
            const message = typeof value === 'string' ? value : value.title ?? value.message ?? key;
            const messageKey = `${key}:${message}`;

            if (displayedMessages.has(messageKey)) continue;

            // Show toast using vue-sonner
            switch (key) {
                case 'success':
                case 'message':
                    toast.success(message);
                    break;
                case 'error':
                    toast.error(message);
                    break;
                case 'warning':
                    toast.warning(message);
                    break;
                case 'info':
                case 'status':
                    toast.info(message);
                    break;
                default:
                    toast(message);
            }

            // Add to displayed set and schedule cleanup
            displayedMessages.add(messageKey);
            setTimeout(() => displayedMessages.delete(messageKey), 10000);
        }
    });
}
