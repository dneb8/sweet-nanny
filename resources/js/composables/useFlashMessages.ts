import { usePage } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import { useNotify } from './useNotify';

/**
 * Composable to automatically handle flash messages from the backend
 * and display them as custom toasts with Iconify icons.
 *
 * This should be called at the root level (in app.ts) to ensure it works
 * with both full page loads and partial Inertia reloads.
 */
export function useFlashMessages() {
    const page = usePage();
    const { notifySuccess, notifyError, notifyWarning, notifyInfo } = useNotify();

    // Track displayed messages to prevent duplicates
    const displayedMessages = new Set<string>();

    watchEffect(() => {
        const flash = (page.props.flash ?? {}) as Record<string, any>;

        // Process all flash message types
        for (const key of ['success', 'error', 'warning', 'info', 'status', 'message']) {
            const value = flash[key];
            if (!value) continue;

            // Extract title and description
            const title = typeof value === 'string' ? value : value.title ?? value.message ?? key;
            const description = typeof value === 'string' ? undefined : value.description;
            const icon = typeof value === 'string' ? undefined : value.icon;

            // Generate unique key for deduplication
            const messageKey = `${key}:${title}:${description || ''}`;

            if (displayedMessages.has(messageKey)) continue;

            // Show toast using unified notify system
            switch (key) {
                case 'success':
                case 'message':
                    notifySuccess(title, description, icon);
                    break;
                case 'error':
                    notifyError(title, description, icon);
                    break;
                case 'warning':
                    notifyWarning(title, description, icon);
                    break;
                case 'info':
                case 'status':
                    notifyInfo(title, description, icon);
                    break;
            }

            // Add to displayed set and schedule cleanup
            displayedMessages.add(messageKey);
            setTimeout(() => displayedMessages.delete(messageKey), 10000);
        }
    });
}
