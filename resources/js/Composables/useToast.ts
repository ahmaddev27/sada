import { useToastStore } from '@/Stores/ui';

// Convenience wrapper so components don't import the store directly
export function useToast() {
    const toast = useToastStore();
    return {
        success: toast.success,
        error:   toast.error,
        warning: toast.warning,
        info:    toast.info,
    };
}
