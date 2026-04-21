import type { PageProps } from './index';

declare module '@inertiajs/vue3' {
    interface PageProps extends PageProps {}
}
