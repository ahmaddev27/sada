import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';

// Apply saved theme before any component mounts to avoid flash on auth/landing pages
const savedTheme = (localStorage.getItem('sada-theme') as 'light' | 'dark') ?? 'light';
document.documentElement.setAttribute('data-theme', savedTheme);

createInertiaApp({
    title: (title) => (title ? `${title} — صدى` : 'صدى'),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: '#0F6F5C',
        includeCSS: true,
        showSpinner: false,
    },
});
