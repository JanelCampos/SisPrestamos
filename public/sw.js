self.addEventListener('push', function (event) {

    if (!event.data) {
        return;
    }

    let data = {};

    try {
        data = event.data.json();
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
            url: data.url || '/'
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

    const url =
        event.notification.data?.url || '/';

    event.waitUntil(

        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function (clientList) {

            for (const client of clientList) {

                if ('postMessage' in client) {

                    client.postMessage({
                        action: 'redirect-from-notificationclick',
                        url: url
                    });

                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(url);
            }

        })
    );
});