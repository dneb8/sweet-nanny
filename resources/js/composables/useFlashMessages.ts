import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useToast } from './useToast';

/**
 * Composable to automatically handle flash messages from the backend
 * and display them as toasts.
 *
 * Call this in your layout components to enable automatic toast notifications.
 */
export function useFlashMessages() {
    const page = usePage();
    const { success, error, warning, info } = useToast();

    // Track displayed messages to prevent duplicates
    const displayedMessages = new Set<string>();

    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return;

            // Handle 'message' object with title and description
            if (flash.message && typeof flash.message === 'object' && 'title' in flash.message) {
                const messageKey = `message:${flash.message.title}:${flash.message.description || ''}`;
                if (!displayedMessages.has(messageKey)) {
                    success(flash.message.title, flash.message.description);
                    displayedMessages.add(messageKey);
                    setTimeout(() => displayedMessages.delete(messageKey), 10000);
                }
            }

            // Handle success messages
            if (flash.success) {
                const messageKey = `success:${flash.success}`;
                if (!displayedMessages.has(messageKey)) {
                    success(flash.success);
                    displayedMessages.add(messageKey);
                    setTimeout(() => displayedMessages.delete(messageKey), 10000);
                }
            }

            // Handle status messages (treat as success)
            if (flash.status) {
                const messageKey = `status:${flash.status}`;
                if (!displayedMessages.has(messageKey)) {
                    success(flash.status);
                    displayedMessages.add(messageKey);
                    setTimeout(() => displayedMessages.delete(messageKey), 10000);
                }
            }

            // Handle error messages
            if (flash.error) {
                const messageKey = `error:${flash.error}`;
                if (!displayedMessages.has(messageKey)) {
                    error(flash.error);
                    displayedMessages.add(messageKey);
                    setTimeout(() => displayedMessages.delete(messageKey), 10000);
                }
            }

            // Handle warning messages
            if (flash.warning) {
                const messageKey = `warning:${flash.warning}`;
                if (!displayedMessages.has(messageKey)) {
                    warning(flash.warning);
                    displayedMessages.add(messageKey);
                    setTimeout(() => displayedMessages.delete(messageKey), 10000);
                }
            }

            // Handle info messages
            if (flash.info) {
                const messageKey = `info:${flash.info}`;
                if (!displayedMessages.has(messageKey)) {
                    info(flash.info);
                    displayedMessages.add(messageKey);
                    setTimeout(() => displayedMessages.delete(messageKey), 10000);
                }
            }
        },
        { immediate: true, deep: true }
    );
}
