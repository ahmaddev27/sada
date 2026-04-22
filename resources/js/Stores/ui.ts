import { defineStore } from 'pinia';
import { ref } from 'vue';

type ToastVariant = 'success' | 'error' | 'warning' | 'info';
type Theme = 'light' | 'dark';

interface ToastItem {
    id:      number;
    message: string;
    variant: ToastVariant;
}

let nextId = 0;

export const useUiStore = defineStore('ui', () => {
    const items       = ref<ToastItem[]>([]);
    const sidebarOpen = ref(true);
    const theme       = ref<Theme>(
        (localStorage.getItem('sada-theme') as Theme) || 'light'
    );

    // Apply theme on store init
    document.documentElement.setAttribute('data-theme', theme.value);

    function applyTheme(t: Theme): void {
        theme.value = t;
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('sada-theme', t);
    }

    function toggleTheme(): void {
        applyTheme(theme.value === 'light' ? 'dark' : 'light');
    }

    function toggleSidebar(): void {
        sidebarOpen.value = !sidebarOpen.value;
    }

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

    return {
        items, sidebarOpen, theme,
        applyTheme, toggleTheme, toggleSidebar,
        add, remove, success, error, warning, info,
    };
});

// Keep backward-compat alias for any code that imports useToastStore
export const useToastStore = useUiStore;
