import { ref } from 'vue';
import { getToken, deleteToken } from 'firebase/messaging';
import { messagingPromise } from '@/firebase';

const FCM_VAPID_KEY  = 'BOdUGS90dssU5sT5P3BM1j9tti0CcHiN2eRe2LLA9sclXHhgyy-_1HVCcneZ37hKVY19DUPdmNF2hvce5bONP9w';
const ASKED_KEY      = 'push_permission_asked';
const OPTED_IN_KEY   = 'push_opted_in';
const REFRESH_KEY    = 'push_last_refresh';
const REFRESH_TTL_MS = 24 * 60 * 60 * 1000; // refresh token once per day max

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

async function getSW(): Promise<ServiceWorkerRegistration | null> {
    try {
        await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        return navigator.serviceWorker.ready;
    } catch {
        return null;
    }
}

/** Try to register the FCM token silently. Returns true if successful.
 *  Skips the network call if we already refreshed within the last 24 hours. */
async function tryRegisterFcm(force = false): Promise<boolean> {
    // Throttle: don't re-register more than once per day
    if (!force) {
        const last = Number(localStorage.getItem(REFRESH_KEY) ?? 0);
        if (Date.now() - last < REFRESH_TTL_MS) return true;
    }

    const messaging = await messagingPromise;
    if (!messaging) return false;

    const sw = await getSW();
    if (!sw) return false;

    try {
        const token = await getToken(messaging, { vapidKey: FCM_VAPID_KEY, serviceWorkerRegistration: sw });
        if (token) {
            await postJson('/push/subscribe', { fcm_token: token });
            localStorage.setItem(REFRESH_KEY, String(Date.now()));
            return true;
        }
    } catch { /* HTTP or other environment — ignore */ }

    return false;
}

/** Try to remove the FCM token. */
async function tryUnregisterFcm(): Promise<void> {
    const messaging = await messagingPromise;
    if (!messaging) return;

    const sw = await getSW();
    if (!sw) return;

    try {
        const token = await getToken(messaging, { vapidKey: FCM_VAPID_KEY, serviceWorkerRegistration: sw });
        if (!token) return;

        await fetch('/push/subscribe', {
            method:  'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body:    JSON.stringify({ fcm_token: token }),
        });

        await deleteToken(messaging);
    } catch { /* ignore */ }
}

export function usePushNotifications() {
    // supported is always true — in-app (database) notifications work everywhere.
    // FCM browser push is layered on top when HTTPS is available.
    const supported  = ref(true);
    const subscribed = ref(false);
    const loading    = ref(false);

    // ── Check current state (used in Settings) ────────────────────────
    async function init(): Promise<void> {
        // User is considered subscribed if they opted in (localStorage flag)
        subscribed.value = localStorage.getItem(OPTED_IN_KEY) === '1';

        // Silently refresh FCM token if already opted in + HTTPS available
        if (subscribed.value) {
            await tryRegisterFcm();
        }
    }

    // ── Auto-init from AppLayout ──────────────────────────────────────
    // - Opted in already → refresh FCM token silently
    // - First time + HTTPS → ask for browser permission once
    async function autoInit(): Promise<void> {
        if (localStorage.getItem(OPTED_IN_KEY) === '1') {
            subscribed.value = true;
            await tryRegisterFcm();
            return;
        }

        // First time: try browser permission (only works on HTTPS)
        if (!localStorage.getItem(ASKED_KEY)) {
            localStorage.setItem(ASKED_KEY, '1');
            const permission = window.Notification?.permission;
            if (permission === 'granted') {
                await _doSubscribe();
            } else if (permission === 'default') {
                // Ask the browser — on HTTP the dialog will fire but getToken will fail;
                // we still mark opted-in for in-app notifications.
                await subscribe();
            }
        }
    }

    // ── Internal subscribe (shared by subscribe + autoInit) ───────────
    async function _doSubscribe(): Promise<void> {
        // Mark opted-in for in-app notifications regardless of FCM outcome
        localStorage.setItem(OPTED_IN_KEY, '1');
        localStorage.setItem(ASKED_KEY, '1');
        subscribed.value = true;

        // Best-effort FCM registration (silently fails on HTTP)
        await tryRegisterFcm(true); // force=true: always save on explicit opt-in
    }

    // ── Explicit subscribe (user clicks "تفعيل" in Settings) ─────────
    async function subscribe(): Promise<void> {
        loading.value = true;
        try {
            // Request browser permission — works even on HTTP (just means
            // the browser prompt fires; FCM token step will fail on HTTP,
            // but in-app notifications are enabled either way)
            if (window.Notification && window.Notification.permission === 'default') {
                await window.Notification.requestPermission();
            }
            await _doSubscribe();
        } finally {
            loading.value = false;
        }
    }

    // ── Explicit unsubscribe (user clicks "إيقاف" in Settings) ───────
    async function unsubscribe(): Promise<void> {
        loading.value = true;
        try {
            localStorage.removeItem(OPTED_IN_KEY);
            subscribed.value = false;
            await tryUnregisterFcm();
        } finally {
            loading.value = false;
        }
    }

    return { supported, subscribed, loading, init, autoInit, subscribe, unsubscribe };
}
