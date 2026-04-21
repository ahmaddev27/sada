import { defineStore } from 'pinia';
import { ref } from 'vue';

type ToastVariant = 'success' | 'error' | 'warning' | 'info';

interface ToastItem {
    id:      number;
    message: string;
    variant: ToastVariant;
}

let nextId = 0;

export const useToastStore = defineStore('ui.toast', () => {
    const items         = ref<ToastItem[]>([]);
    const sidebarOpen   = ref(true);

    function add(message: string, variant: ToastVariant = 'info', duration = 4000): void {
        const id = ++nextId;
        items.value.push({ id, message, variant });
        setTimeout(() => remove(id), duration);
    }

    function remove(id: number): void {
        items.value = items.value.filter(t => t.id !== id);
    }

    function success(message: string): void { add(message, 'success'); }
    function error(message: string): void   { add(message, 'error'); }
    function warning(message: string): void { add(message, 'warning'); }
    function info(message: string): void    { add(message, 'info'); }

    function toggleSidebar(): void { sidebarOpen.value = !sidebarOpen.value; }

    return { items, sidebarOpen, add, remove, success, error, warning, info, toggleSidebar };
});
