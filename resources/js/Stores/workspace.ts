import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { Workspace } from '@/Types';

export const useWorkspaceStore = defineStore('workspace', () => {
    const current    = ref<Workspace | null>(null);
    const workspaces = ref<Workspace[]>([]);

    const hasWorkspace = computed(() => current.value !== null);

    function setCurrent(w: Workspace): void {
        current.value = w;
    }

    function setWorkspaces(list: Workspace[]): void {
        workspaces.value = list;
        if (list.length > 0 && !current.value) {
            current.value = list[0];
        }
    }

    function clear(): void {
        current.value    = null;
        workspaces.value = [];
    }

    return { current, workspaces, hasWorkspace, setCurrent, setWorkspaces, clear };
});
