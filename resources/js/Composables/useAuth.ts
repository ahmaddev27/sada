import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/Types';

export function useAuth() {
    const page = usePage();

    const user            = computed(() => (page.props as any).auth?.user as User | null);
    const isAuthenticated = computed(() => user.value !== null);
    const tokenBalance    = computed(() => user.value?.token_balance ?? 0);

    return { user, isAuthenticated, tokenBalance };
}
