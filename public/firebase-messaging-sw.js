importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey:            'AIzaSyC-9dexx-m1_tyAQvQ0LJD3SvqVYMcBnNw',
    authDomain:        'sada-87617.firebaseapp.com',
    projectId:         'sada-87617',
    storageBucket:     'sada-87617.firebasestorage.app',
    messagingSenderId: '555144333182',
    appId:             '1:555144333182:web:44b2512660b3e16770152f',
});

const messaging = firebase.messaging();

// Background message handler (tab not in focus)
messaging.onBackgroundMessage((payload) => {
    const { title, body } = payload.notification ?? {};

    self.registration.showNotification(title || 'صدى', {
        body:  body  || '',
        icon:  '/favicon.svg',
        badge: '/favicon.svg',
        data:  { url: payload.data?.url || '/' },
        dir:   'rtl',
        lang:  'ar',
    });
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if ('focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
