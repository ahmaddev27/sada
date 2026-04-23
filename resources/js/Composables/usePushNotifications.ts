import { ref } from 'vue';
import { getToken, deleteToken } from 'firebase/messaging';
import { messagingPromise } from '@/firebase';

const FCM_VAPID_KEY = '6sp9Bcy2DrT3nFY11dGz4Vvo5RT_jJvBMT5KtXCTGQk';

function getCsrf(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';
}

async function postJson(url: string, body: object): Promise<void> {
    await fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
        body:    JSON.stringify(body),
    });
}

export function usePushNotifications() {
    const supported  = ref(false);
    const subscribed = ref(false);
    const loading    = ref(false);

    async function init(): Promise<void> {
        const messaging = await messagingPromise;
        if (!messaging) return;

        supported.value = true;

        await navigator.serviceWorker.register('/firebase-messaging-sw.js');

        try {
            const token = await getToken(messaging, { vapidKey: FCM_VAPID_KEY });
            subscribed.value = !!token;
        } catch {
            subscribed.value = false;
        }
    }

    async function subscribe(): Promise<void> {
        const messaging = await messagingPromise;
        if (!messaging) return;

        loading.value = true;
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') return;

            const token = await getToken(messaging, {
                vapidKey:                    FCM_VAPID_KEY,
                serviceWorkerRegistration:   await navigator.serviceWorker.ready,
            });

            await postJson('/push/subscribe', { fcm_token: token });
            subscribed.value = true;
        } finally {
            loading.value = false;
        }
    }

    async function unsubscribe(): Promise<void> {
        const messaging = await messagingPromise;
        if (!messaging) return;

        loading.value = true;
        try {
            const token = await getToken(messaging, { vapidKey: FCM_VAPID_KEY });
            if (!token) return;

            await fetch('/push/subscribe', {
                method:  'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body:    JSON.stringify({ fcm_token: token }),
            });

            await deleteToken(messaging);
            subscribed.value = false;
        } finally {
            loading.value = false;
        }
    }

    return { supported, subscribed, loading, init, subscribe, unsubscribe };
}
