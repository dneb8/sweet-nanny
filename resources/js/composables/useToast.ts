import { toast } from 'vue-sonner';

export function useToast() {
    const success = (message: string, description?: string) => {
        toast.success(message, {
            description,
            duration: 5000,
        });
    };

    const error = (message: string, description?: string) => {
        toast.error(message, {
            description,
            duration: 6000,
        });
    };

    const warning = (message: string, description?: string) => {
        toast.warning(message, {
            description,
            duration: 5000,
        });
    };

    const info = (message: string, description?: string) => {
        toast.info(message, {
            description,
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
