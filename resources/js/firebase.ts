import { initializeApp, getApps } from 'firebase/app';
import { getMessaging, isSupported } from 'firebase/messaging';
import type { Messaging } from 'firebase/messaging';

const firebaseConfig = {
    apiKey:            'AIzaSyC-9dexx-m1_tyAQvQ0LJD3SvqVYMcBnNw',
    authDomain:        'sada-87617.firebaseapp.com',
    projectId:         'sada-87617',
    storageBucket:     'sada-87617.firebasestorage.app',
    messagingSenderId: '555144333182',
    appId:             '1:555144333182:web:44b2512660b3e16770152f',
};

const app = getApps().length ? getApps()[0] : initializeApp(firebaseConfig);

// Messaging requires HTTPS + service worker support — resolve to null if unsupported
export const messagingPromise: Promise<Messaging | null> = isSupported()
    .then(ok => ok ? getMessaging(app) : null)
    .catch(() => null);
