self.addEventListener('push', function (event) {

    if (!event.data) {
        return;
    }

    let data = {};

    try {
        data = event.data.json();
        console.log(
            'Payload Push recibido:',
            data
        );
    } catch (error) {
        data = {
            title: 'SisPrestamos',
            message: event.data.text()
        };
    }

    const title = data.title || 'SisPrestamos';

    const options = {
        body: data.message || '',
        icon: '/icon-192.png',
        badge: '/icon-192.png',
        data: {
            url: data.url || '/',
            notificationId: data.notificationId || null
        }
    };

    event.waitUntil(
        self.registration.showNotification(
            title,
            options
        )
    );
});


self.addEventListener('notificationclick', function (event) {

    event.notification.close();

    const data =
        event.notification.data || {};

    const baseUrl =
        data.url || '/';

    const notificationId =
        data.notificationId || null;

    let url = baseUrl;

    if (notificationId) {

        const separator =
            baseUrl.includes('?') ? '&' : '?';

        url =
            baseUrl
            + separator
            + 'notificacion='
            + encodeURIComponent(notificationId);
    }

    event.waitUntil(

        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function (clientList) {

            for (const client of clientList) {

                if ('focus' in client) {

                    client.focus();

                    client.postMessage({
                        action: 'push-notification-click',
                        url: url
                    });

                    return;
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});