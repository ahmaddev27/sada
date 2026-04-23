import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Workspace } from '@/Types';

export function useWorkspace() {
    const page = usePage();

    const workspace  = computed(() => (page.props as any).workspace as Workspace | null);
    const workspaces = computed(() => (page.props as any).workspaces as Workspace[] ?? []);

    return { workspace, workspaces };
}
