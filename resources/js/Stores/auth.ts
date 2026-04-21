import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { User } from '@/Types';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);

    const isAuthenticated = computed(() => user.value !== null);
    const tokenBalance    = computed(() => user.value?.token_balance ?? 0);

    function setUser(u: User | null): void {
        user.value = u;
    }

    function clear(): void {
        user.value = null;
    }

    return { user, isAuthenticated, tokenBalance, setUser, clear };
});
