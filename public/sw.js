/*
 * SisPrestamos - Service Worker
 * Versión de prueba para Web Push
 */

console.log('🔥 SW NUEVO CARGADOo');


/*
 * Activa inmediatamente la nueva versión
 * del Service Worker.
 */
self.addEventListener('install', function (event) {

    console.log('🔥 SW instalando nueva versión...');

    self.skipWaiting();
});


/*
 * Hace que la nueva versión tome el control
 * de las páginas abiertas.
 */
self.addEventListener('activate', function (event) {

    event.waitUntil(

        clients.claim().then(function () {

            console.log(
                '🔥 SW nueva versión activa y controlando clientes.'
            );

        })

    );
});


/*
 * RECIBIR NOTIFICACIÓN PUSH
 */
self.addEventListener('push', function (event) {

    if (!event.data) {
        return;
    }

    let data = {};

    try {

        data = event.data.json();

        console.log(
            '🔥 Payload Push recibido:',
            data
        );

    } catch (error) {

        data = {
            title: 'SisPrestamos',
            message: event.data.text()
        };

        console.log(
            '🔥 Payload Push recibido como texto:',
            data
        );
    }

    const title =
        data.title || 'SisPrestamos';

    const options = {

        body:
            data.message || '',

        icon:
            '/icon-192.png',

        badge:
            '/icon-192.png',

        data: {

            url:
                data.url || '/',

            notificationId:
                data.notificationId || null

        }
    };

    console.log(
        '🔥 Datos guardados en la notificación:',
        options.data
    );

    event.waitUntil(

        self.registration.showNotification(
            title,
            options
        )

    );
});


/*
 * CLIC EN LA NOTIFICACIÓN
 */
self.addEventListener('notificationclick', function (event) {

    event.notification.close();

    const data =
        event.notification.data || {};

    const baseUrl =
        data.url || '/';

    const notificationId =
        data.notificationId || null;

    console.log(
        '🔥 CLICK PUSH - data:',
        data
    );

    console.log(
        '🔥 CLICK PUSH - notificationId:',
        notificationId
    );

    let url =
        baseUrl;

    /*
     * Agregamos el ID de la notificación
     * a la URL.
     */
    if (notificationId) {

        const separator =
            baseUrl.includes('?')
                ? '&'
                : '?';

        url =
            baseUrl
            + separator
            + 'notificacion='
            + encodeURIComponent(
                notificationId
            );
    }

    console.log(
        '🔥 CLICK PUSH - URL FINAL:',
        url
    );

    event.waitUntil(

        clients.matchAll({

            type: 'window',

            includeUncontrolled: true

        }).then(function (clientList) {

            /*
             * Si ya existe una ventana de SisPrestamos,
             * le enviamos la URL.
             */
            for (const client of clientList) {

                if (
                    client.url.startsWith(
                        self.location.origin
                    )
                ) {

                    client.focus();

                    client.postMessage({

                        action:
                            'push-notification-click',

                        url:
                            url

                    });

                    return;
                }
            }

            /*
             * Si no existe ninguna ventana abierta,
             * abrimos una nueva.
             */
            if (clients.openWindow) {

                return clients.openWindow(
                    url
                );
            }

        })
    );
});