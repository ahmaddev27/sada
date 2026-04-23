import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const VAPID_PUBLIC_KEY = (window as any).__vapid_public_key__ as string | undefined;

function urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = window.atob(base64);
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

export function usePushNotifications() {
    const supported   = ref('serviceWorker' in navigator && 'PushManager' in window);
    const subscribed  = ref(false);
    const loading     = ref(false);

    async function register(): Promise<ServiceWorkerRegistration | null> {
        if (!supported.value) return null;
        return navigator.serviceWorker.register('/sw.js');
    }

    async function getSubscription(): Promise<PushSubscription | null> {
        const reg = await navigator.serviceWorker.ready;
        return reg.pushManager.getSubscription();
    }

    async function subscribe(): Promise<void> {
        if (!supported.value || !VAPID_PUBLIC_KEY) return;
        loading.value = true;

        try {
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.subscribe({
                userVisibleOnly:      true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
            });

            const json = sub.toJSON() as { endpoint: string; keys: { p256dh: string; auth: string } };

            await fetch('/push/subscribe', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body:    JSON.stringify({ endpoint: json.endpoint, keys: json.keys }),
            });

            subscribed.value = true;
        } finally {
            loading.value = false;
        }
    }

    async function unsubscribe(): Promise<void> {
        loading.value = true;
        try {
            const sub = await getSubscription();
            if (!sub) return;

            await fetch('/push/subscribe', {
                method:  'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body:    JSON.stringify({ endpoint: sub.endpoint }),
            });

            await sub.unsubscribe();
            subscribed.value = false;
        } finally {
            loading.value = false;
        }
    }

    async function init(): Promise<void> {
        if (!supported.value) return;
        await register();
        const sub = await getSubscription();
        subscribed.value = !!sub;
    }

    function getCsrf(): string {
        return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';
    }

    return { supported, subscribed, loading, init, subscribe, unsubscribe };
}
