async function activarNotificacionesPush() {

    try {

        console.log('PUSH 1: iniciando activación');

        // Verificar notificaciones
        if (!('Notification' in window)) {
            throw new Error(
                'Este navegador no soporta notificaciones.'
            );
        }

        console.log('PUSH 2: Notification soportado');


        // Verificar Service Worker
        if (!('serviceWorker' in navigator)) {
            throw new Error(
                'Este navegador no soporta Service Worker.'
            );
        }

        console.log('PUSH 3: Service Worker soportado');


        // Pedir permiso
        const permiso =
            await Notification.requestPermission();

        console.log(
            'PUSH 4: permiso =',
            permiso
        );

        if (permiso !== 'granted') {
            throw new Error(
                'No se concedió permiso para las notificaciones.'
            );
        }


        // Registrar Service Worker
        console.log(
            'PUSH 5: registrando Service Worker...'
        );

        const registration =
            await navigator.serviceWorker.register(
                './sw.js'
            );

        console.log(
            'PUSH 6: Service Worker registrado',
            registration
        );


        // Obtener suscripción existente
        console.log(
            'PUSH 7: buscando suscripción existente...'
        );

        let subscription =
            await registration.pushManager.getSubscription();

        console.log(
            'PUSH 8: suscripción existente =',
            subscription
        );


        // Crear suscripción
        if (!subscription) {

            console.log(
                'PUSH 9: creando nueva suscripción...'
            );

            subscription =
                await registration.pushManager.subscribe({

                    userVisibleOnly: true,

                    applicationServerKey:
                        urlBase64ToUint8Array(
                            window.VAPID_PUBLIC_KEY
                        )
                });

            console.log(
                'PUSH 10: suscripción creada',
                subscription
            );
        }


        // Convertir a JSON
        const subscriptionJson =
            subscription.toJSON();

        console.log(
            'PUSH 11: subscription JSON',
            subscriptionJson
        );


        // Enviar al servidor
        console.log(
            'PUSH 12: enviando suscripción al servidor...'
        );

        const response = await fetch(
            './api/push/subscribe',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({

                    endpoint:
                        subscriptionJson.endpoint,

                    p256dh:
                        subscriptionJson.keys.p256dh,

                    auth:
                        subscriptionJson.keys.auth
                })
            }
        );

        console.log(
            'PUSH 13: respuesta HTTP =',
            response.status
        );


        const result =
            await response.json();

        console.log(
            'PUSH 14: respuesta PHP =',
            result
        );


        if (!response.ok || !result.success) {

            throw new Error(
                result.message ||
                'No se pudo guardar la suscripción.'
            );
        }


        console.log(
            'PUSH 15: TODO CORRECTO'
        );

        alert(
            '¡Notificaciones activadas correctamente!'
        );


    } catch (error) {

        console.error(
            '========== ERROR PUSH =========='
        );

        console.error(
            'Nombre:',
            error?.name
        );

        console.error(
            'Mensaje:',
            error?.message
        );

        console.error(
            'Stack:',
            error?.stack
        );

        console.error(
            'Objeto completo:',
            error
        );

        console.error(
            '================================'
        );

        alert(
            'Error Push: ' +
            (error?.message || error)
        );
    }
}


/**
 * Convierte la clave pública VAPID
 * de Base64 URL a Uint8Array.
 */
function urlBase64ToUint8Array(base64String) {

    const padding = '='.repeat(
        (4 - base64String.length % 4) % 4
    );

    const base64 = (
        base64String +
        padding
    )
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData =
        window.atob(base64);

    return Uint8Array.from(
        [...rawData].map(
            char => char.charCodeAt(0)
        )
    );
}


/**
 * Conectamos el botón.
 */
document.addEventListener(
    'DOMContentLoaded',
    () => {

        const boton =
            document.getElementById(
                'btnActivarNotificaciones'
            );

        if (!boton) {
            return;
        }

        boton.addEventListener(
            'click',
            activarNotificacionesPush
        );
    }
);