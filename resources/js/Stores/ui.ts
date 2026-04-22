import { defineStore } from 'pinia';
import { ref } from 'vue';

type ToastVariant = 'success' | 'error' | 'warning' | 'info';
type Theme = 'light' | 'dark';

interface ToastItem {
    id:      number;
    title:   string;
    desc?:   string;
    variant: ToastVariant;
}

let nextId = 0;

export const useUiStore = defineStore('ui', () => {
    const items       = ref<ToastItem[]>([]);
    const sidebarOpen = ref(true);
    const theme       = ref<Theme>(
        (localStorage.getItem('sada-theme') as Theme) || 'light'
    );

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

    function add(title: string, variant: ToastVariant = 'info', duration = 4000, desc?: string): void {
        const id = ++nextId;
        items.value.push({ id, title, desc, variant });
        setTimeout(() => remove(id), duration);
    }

    function remove(id: number): void {
        items.value = items.value.filter(t => t.id !== id);
    }

    function success(title: string, desc?: string): void { add(title, 'success', 4000, desc); }
    function error(title: string, desc?: string): void   { add(title, 'error',   4000, desc); }
    function warning(title: string, desc?: string): void { add(title, 'warning', 4000, desc); }
    function info(title: string, desc?: string): void    { add(title, 'info',    4000, desc); }

    return {
        items, sidebarOpen, theme,
        applyTheme, toggleTheme, toggleSidebar,
        add, remove, success, error, warning, info,
    };
});

// Keep backward-compat alias for any code that imports useToastStore
export const useToastStore = useUiStore;
