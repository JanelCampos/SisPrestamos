<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? config('app.name')) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sp-primary: #0b3954;
            --sp-accent: #f0a202;
            --sp-surface: #ffffff;
            --sp-soft: #f4f7fb;
            --sp-border: #dbe5f0;
        }

        body {
            background: linear-gradient(180deg, #eff4f9 0%, #f9fbfd 100%);
            color: #16324f;
        }

        .sidebar {
            background: linear-gradient(180deg, #0b3954 0%, #0f4c75 100%);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.86);
            border-radius: 12px;
            padding: .75rem 1rem;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,.13);
            color: #fff;
        }

        .card-soft {
            border: 1px solid var(--sp-border);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(11,57,84,.08);
        }

        .kpi-value {
            font-size: 1.85rem;
            font-weight: 700;
        }

        .table thead th {
            background: #f5f8fc;
            font-size: .84rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .badge-soft {
            background: rgba(11,57,84,.08);
            color: var(--sp-primary);
        }

        .page-header {
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(219,229,240,.8);
            border-radius: 1rem;

            /* Permite que el menú de notificaciones quede encima */
            position: relative;
            z-index: 1050;
        }

        /* =====================================================
           SOLO MODIFICACIONES PARA MÓVIL
           ===================================================== */

        .mobile-menu-button {
            background: #0b3954;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 1.3rem;
        }

        .mobile-menu-button:hover,
        .mobile-menu-button:focus {
            background: #0f4c75;
            color: #fff;
        }

        .notification-container {
            position: relative;
            z-index: 2000;
        }

        #notificationDropdown {
            z-index: 2001 !important;
        }

        .notification-unread {
            background-color: #f1f3f5;
            border-left: 4px solid #0d6efd;
        }

        .notification-unread .notification-title {
            font-weight: 600;
        }

        .notification-unread::before {
            content: '';
            width: 7px;
            height: 7px;
            background-color: #0d6efd;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        /* En celular el sidebar funciona como menú lateral */
        @media (max-width: 991.98px) {

            .mobile-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                height: 100vh;
                z-index: 3000;

                transform: translateX(-100%);
                transition: transform .3s ease;

                overflow-y: auto;
            }

            .mobile-sidebar.show {
                transform: translateX(0);
            }

            /* Fondo oscuro detrás del menú */
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, .45);
                z-index: 2990;  
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* En escritorio no se aplican las modificaciones móviles */
        @media (min-width: 992px) {

            .mobile-menu-button,
            .mobile-close-button,
            .sidebar-overlay {
                display: none !important;
            }

        }
    </style>
</head>

<body>

<?php if ($user): ?>

    <div class="container-fluid">

        <div class="row">

            <!-- =====================================================
                 BOTÓN HAMBURGUESA - SOLO MÓVIL
                 ===================================================== -->

            <div class="d-lg-none p-3">

                <button
                    type="button"
                    class="mobile-menu-button"
                    id="mobileMenuButton"
                    aria-label="Abrir menú">

                    <i class="bi bi-list"></i>

                </button>

            </div>


            <!-- =====================================================
                 FONDO OSCURO CUANDO EL MENÚ ESTÁ ABIERTO
                 ===================================================== -->

            <div
                class="sidebar-overlay"
                id="sidebarOverlay">
            </div>


            <!-- =====================================================
                 SIDEBAR
                 ===================================================== -->

            <aside
                class="col-12 col-lg-2 sidebar p-4 mobile-sidebar"
                id="sidebarMenu">

                <!-- CABECERA -->

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div
                        class="bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">

                        <i class="bi bi-cash-coin"></i>

                    </div>

                    <div>

                        <div class="text-white fw-semibold">
                            <?= e(config('app.name')) ?>
                        </div>

                        <small class="text-white-50">
                            Control crediticio
                        </small>

                    </div>


                    <!-- BOTÓN CERRAR SOLO EN MÓVIL -->

                    <button
                        type="button"
                        class="btn-close btn-close-white ms-auto d-lg-none mobile-close-button"
                        id="mobileCloseButton"
                        aria-label="Cerrar menú">
                    </button>

                </div>


                <!-- =================================================
                     NAVEGACIÓN ORIGINAL
                     ================================================= -->

                <nav class="nav flex-column gap-2">

                    <a
                        class="nav-link <?= is_active_route('/dashboard') ? 'active' : '' ?>"
                        href="<?= e(app_url('dashboard')) ?>">

                        <i class="bi bi-speedometer2 me-2"></i>Dashboard

                    </a>

                    <a
                        class="nav-link <?= is_active_route('/clientes') ? 'active' : '' ?>"
                        href="<?= e(app_url('clientes')) ?>">

                        <i class="bi bi-people me-2"></i>Clientes

                    </a>

                    <a
                        class="nav-link <?= is_active_route('/prestamos') ? 'active' : '' ?>"
                        href="<?= e(app_url('prestamos')) ?>">

                        <i class="bi bi-wallet2 me-2"></i>Prestamos

                    </a>

                    <?php if (\App\Auth::hasRole('administrador total')): ?>

                        <a
                            class="nav-link <?= is_active_route('/solicitudes-cobro') ? 'active' : '' ?>"
                            href="<?= e(app_url('solicitudes-cobro')) ?>">

                            <i class="bi bi-cash-coin me-2"></i>Solicitudes de cobro

                        </a>

                    <?php endif; ?>

                    <a
                        class="nav-link <?= is_active_route('/reportes') ? 'active' : '' ?>"
                        href="<?= e(app_url('reportes')) ?>">

                        <i class="bi bi-graph-up-arrow me-2"></i>Reportes

                    </a>

                    <?php if (\App\Auth::hasRole('administrador total')): ?>

                        <a
                            class="nav-link <?= is_active_route('/usuarios') ? 'active' : '' ?>"
                            href="<?= e(app_url('usuarios')) ?>">

                            <i class="bi bi-shield-lock me-2"></i>Usuarios

                        </a>

                        <a
                            class="nav-link <?= is_active_route('/configuracion') ? 'active' : '' ?>"
                            href="<?= e(app_url('configuracion')) ?>">

                            <i class="bi bi-sliders me-2"></i>Configuracion

                        </a>

                    <?php endif; ?>

                </nav>

            </aside>
            <!-- =====================================================
                 CONTENIDO PRINCIPAL
                 ===================================================== -->

            <main class="col-12 col-lg-10 p-4 p-lg-5">

                <div class="page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 px-4 py-3 mb-4">

                    <div>

                        <h1 class="h3 mb-1">
                            <?= e($title ?? '') ?>
                        </h1>

                        <p class="text-secondary mb-0">
                            Gestion integral de cartera, clientes y cobranza.
                        </p>

                    </div>


                    <div class="d-flex align-items-center gap-3">

                        <div class="text-end">
                            <div class="fw-semibold">
                                <?= e($user['nombre_completo'] ?? '') ?>
                            </div>

                            <small class="text-secondary text-uppercase">
                                <?= e($user['role_name'] ?? '') ?>
                            </small>
                        </div>

                        <!-- NOTIFICACIONES -->
                        <div class="dropdown position-relative notification-container">

                            <button
                                type="button"
                                class="btn btn-outline-secondary position-relative"
                                id="notificationButton"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                title="Notificaciones"
                            >
                                <i class="bi bi-bell"></i>

                                <span
                                    id="notificationBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                                >
                                    0
                                </span>
                            </button>

                            <div
                                class="dropdown-menu dropdown-menu-end shadow"
                                id="notificationDropdown"
                                style="width: 360px; max-height: 450px; overflow-y: auto;"
                            >
                                <div class="px-3 py-2 border-bottom">
                                    <strong>Notificaciones</strong>
                                </div>

                                <div id="notificationList">
                                    <div class="text-center text-secondary py-4">
                                        Cargando...
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- NOTIFICACIONES PUSH -->
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="btnActivarNotificaciones"
                            title="Activar notificaciones Push"
                        >
                            <i class="bi bi-bell"></i>
                            Activar
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm"
                            id="btnProbarPush"
                        >
                            <i class="bi bi-send"></i>
                            Probar Push
                        </button>

                        <form method="post" action="<?= e(app_url('logout')) ?>">
                            <?= csrf_field() ?>

                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                Salir
                            </button>
                        </form>

                    </div>

                </div>


                <!-- =====================================================
                     MENSAJES
                     ===================================================== -->

                <?php foreach ($flashMessages as $type => $messages): ?>

                    <?php foreach ($messages as $message): ?>

                        <div
                            class="alert alert-<?= e($type) ?> alert-dismissible fade show"
                            role="alert">

                            <?= e($message) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Cerrar">
                            </button>

                        </div>

                    <?php endforeach; ?>

                <?php endforeach; ?>


                <!-- VISTA -->

                <?php require $viewFile; ?>

            </main>

        </div>

    </div>

<?php else: ?>

    <?php require $viewFile; ?>

<?php endif; ?>

<?php
$pushConfig = require __DIR__ . '/../config/push.php';
?>

<script>
window.VAPID_PUBLIC_KEY = <?= json_encode(
    $pushConfig['vapid']['publicKey']
) ?>;
</script>

<!-- =====================================================
     JAVASCRIPT DEL MENÚ MÓVIL
     ===================================================== -->

<script>

    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileCloseButton = document.getElementById('mobileCloseButton');
    const sidebarMenu = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openMobileMenu() {
        sidebarMenu.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        sidebarMenu.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', openMobileMenu);
    }

    if (mobileCloseButton) {
        mobileCloseButton.addEventListener('click', closeMobileMenu);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeMobileMenu);
    }

    /*
     * Al pulsar una opción del menú en móvil,
     * dejamos que navegue normalmente.
     */
    if (sidebarMenu) {

        sidebarMenu.querySelectorAll('.nav-link').forEach(function(link) {

            link.addEventListener('click', function() {
                closeMobileMenu();
            });

        });

    }

    if ('serviceWorker' in navigator) {

        window.addEventListener('load', async function () {

            try {

                <?php
                $swVersion = filemtime(
                    __DIR__ . '/sw.js'
                );
                ?>  

                const registration =
                    await navigator.serviceWorker.register(
                        '<?= app_url('sw.js').$swVersion ?>'
                    );

                await registration.update();

                console.log(
                    'Service Worker registrado:',
                    registration.scope
                );

            } catch (error) {

                console.error(
                    'Error al registrar el Service Worker:',
                    error
                );
            }
        });
    }

    if ('serviceWorker' in navigator) {

        navigator.serviceWorker.addEventListener(
            'message',
            function (event) {

                if (!event.data) {
                    return;
                }

                if (
                    event.data.action ===
                    'redirect-from-notificationclick'
                ) {

                    window.location.href =
                        event.data.url;
                }
            }
        );
    }

    async function activarNotificacionesPush() {

    if (!('serviceWorker' in navigator)) {
        throw new Error(
            'Este navegador no soporta Service Worker.'
        );
    }

    if (!('PushManager' in window)) {
        throw new Error(
            'Este navegador no soporta Web Push.'
        );
    }

    if (!('Notification' in window)) {
        throw new Error(
            'Este navegador no soporta notificaciones.'
        );
    }

    const permission =
        await Notification.requestPermission();

    if (permission !== 'granted') {
        throw new Error(
            'El permiso para las notificaciones fue rechazado.'
        );
    }

    const registration =
        await navigator.serviceWorker.ready;

    let subscription =
        await registration.pushManager.getSubscription();

    if (!subscription) {

        subscription =
            await registration.pushManager.subscribe({

                userVisibleOnly: true,

                applicationServerKey:
                    urlBase64ToUint8Array(
                        window.VAPID_PUBLIC_KEY
                    )
            });
    }

    const subscriptionJson =
        subscription.toJSON();

    const formData = new URLSearchParams();

    formData.append(
        '_token',
        <?= json_encode(csrf_token()) ?>
    );

    formData.append(
        'endpoint',
        subscriptionJson.endpoint
    );

    formData.append(
        'p256dh',
        subscriptionJson.keys.p256dh
    );

    formData.append(
        'auth',
        subscriptionJson.keys.auth
    );

    const response = await fetch(
        '<?= app_url('api/push/suscripcion') ?>',
        {
            method: 'POST',

            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded;charset=UTF-8'
            },

            body: formData.toString()
        }
    );

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(
            result.message ||
            'No se pudo guardar la suscripción Push.'
        );
    }

    console.log(
        'Suscripción Push guardada correctamente.'
    );

    return subscription;
}


function urlBase64ToUint8Array(base64String) {

    const padding =
        '='.repeat(
            (4 - base64String.length % 4) % 4
        );

    const base64 =
        (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

    const rawData =
        window.atob(base64);

    const outputArray =
        new Uint8Array(rawData.length);

    for (
        let i = 0;
        i < rawData.length;
        ++i
    ) {
        outputArray[i] =
            rawData.charCodeAt(i);
    }

    return outputArray;
}

const btnActivarNotificaciones =
    document.getElementById('btnActivarNotificaciones');

if (btnActivarNotificaciones) {

    btnActivarNotificaciones.addEventListener(
        'click',
        async function () {

            btnActivarNotificaciones.disabled = true;

            const textoOriginal =
                btnActivarNotificaciones.innerHTML;

            btnActivarNotificaciones.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                    role="status"
                    aria-hidden="true">
                </span>
                Activando...
            `;

            try {

                await activarNotificacionesPush();

                btnActivarNotificaciones.innerHTML = `
                    <i class="bi bi-bell-fill"></i>
                    Activadas
                `;

                btnActivarNotificaciones.classList.remove(
                    'btn-outline-primary'
                );

                btnActivarNotificaciones.classList.add(
                    'btn-success'
                );

                console.log(
                    'Notificaciones Push activadas correctamente.'
                );

            } catch (error) {

                console.error(
                    'Error al activar las notificaciones:',
                    error
                );

                btnActivarNotificaciones.innerHTML =
                    textoOriginal;

                alert(
                    error.message ||
                    'No se pudieron activar las notificaciones.'
                );

            } finally {

                btnActivarNotificaciones.disabled = false;
            }
        }
    );
}

const btnProbarPush =
    document.getElementById('btnProbarPush');

if (btnProbarPush) {

    btnProbarPush.addEventListener(
        'click',
        async function () {

            btnProbarPush.disabled = true;

            try {

                const response = await fetch(
                    '<?= e(app_url('api/push/prueba')) ?>',
                    {
                        method: 'POST',

                        headers: {
                            'Accept': 'application/json',
                            'Content-Type':
                                'application/x-www-form-urlencoded;charset=UTF-8'
                        },

                        credentials: 'same-origin',

                        body: new URLSearchParams({
                            _token:
                                '<?= e(csrf_token()) ?>'
                        })
                    }
                );

                const result =
                    await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message ||
                        'No se pudo enviar la notificación Push.'
                    );
                }

                console.log(
                    'Prueba Push enviada correctamente.'
                );

            } catch (error) {

                console.error(
                    'Error al probar Push:',
                    error
                );

                alert(
                    error.message ||
                    'No se pudo enviar la notificación Push.'
                );

            } finally {

                btnProbarPush.disabled = false;
            }
        }
    );
}

</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const notificationBadge = document.getElementById('notificationBadge');
        const notificationList = document.getElementById('notificationList');

        if (!notificationBadge || !notificationList) {
            return;
        }

        /*
        * Obtiene la cantidad de notificaciones no leídas
        */
        async function loadUnreadCount() {

            try {

                const response = await fetch(
                    '<?= e(app_url('/api/notificaciones/no-leidas')) ?>',
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await response.json();

                if (!data.success) {
                    return;
                }

                const count = Number(data.count) || 0;

                if (count > 0) {

                    notificationBadge.textContent =
                        count > 99 ? '99+' : count;

                    notificationBadge.classList.remove('d-none');

                } else {

                    notificationBadge.classList.add('d-none');

                }

            } catch (error) {

                console.error(
                    'Error al cargar contador de notificaciones:',
                    error
                );

            }
        }

        async function marcarNotificacionPushComoLeida() {

            const params = new URLSearchParams(
                window.location.search
            );

            const notificationId =
                params.get('notificacion');

            if (!notificationId) {
                return;
            }

            try {

                const response = await fetch(
                    '<?= e(app_url('/api/notificaciones/')) ?>'
                    + encodeURIComponent(notificationId)
                    + '/leer',
                    {
                        method: 'POST',

                        headers: {
                            'Accept': 'application/json',
                            'Content-Type':
                                'application/x-www-form-urlencoded'
                        },

                        credentials: 'same-origin',

                        body: new URLSearchParams({
                            _token: '<?= e(csrf_token()) ?>'
                        })
                    }
                );

                const data =
                    await response.json();

                if (!response.ok || !data.success) {

                    console.error(
                        'No se pudo marcar como leída la notificación del Push:',
                        data
                    );

                    return;
                }

                console.log(
                    'Notificación Push marcada como leída.'
                );

                // Actualizar inmediatamente el contador.
                loadUnreadCount();

                /*
                * Quitamos el parámetro de la URL para que,
                * si el usuario recarga la página, no volvamos
                * a procesar la misma notificación.
                */
                const cleanUrl =
                    new URL(window.location.href);

                cleanUrl.searchParams.delete(
                    'notificacion'
                );

                window.history.replaceState(
                    {},
                    document.title,
                    cleanUrl.toString()
                );

            } catch (error) {

                console.error(
                    'Error al marcar la notificación Push como leída:',
                    error
                );
            }
        }


        /*
        * Obtiene las notificaciones del usuario
        */
        async function loadNotifications() {

            notificationList.innerHTML = `
                <div class="text-center text-secondary py-4">
                    Cargando...
                </div>
            `;

            try {

                const response = await fetch(
                    '<?= e(app_url('/api/notificaciones')) ?>',
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await response.json();

                if (!data.success) {

                    notificationList.innerHTML = `
                        <div class="text-center text-danger py-4">
                            No se pudieron cargar las notificaciones.
                        </div>
                    `;

                    return;
                }

                const notifications = data.notifications || [];

                if (notifications.length === 0) {

                    notificationList.innerHTML = `
                        <div class="text-center text-secondary py-4">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                            No tienes notificaciones.
                        </div>
                    `;

                    return;
                }

                notificationList.innerHTML = '';

                notifications.forEach(notification => {

                    const item = document.createElement('div');

                    item.className =
                        'px-3 py-3 border-bottom notification-item';

                    if (!Number(notification.leida)) {
                        item.classList.add('notification-unread');
                    }

                    const date = formatNotificationDate(
                        notification.creado_en
                    );

                    item.innerHTML = `
                        <div class="d-flex gap-2">

                            <div class="flex-shrink-0">
                                <i class="bi bi-bell-fill text-primary"></i>
                            </div>

                            <div class="flex-grow-1">

                                <div class="fw-semibold">
                                    ${escapeHtml(notification.titulo)}
                                </div>

                                <div class="small text-secondary mt-1">
                                    ${escapeHtml(notification.mensaje)}
                                </div>

                                <div class="small text-muted mt-2">
                                    ${date}
                                </div>

                            </div>

                        </div>
                    `;

                    notificationList.appendChild(item);

                    item.addEventListener('click', async () => {

                        // Si ya está leída, no necesitamos marcarla nuevamente.
                        // Pero si es una solicitud de cobro, igualmente permitimos
                        // abrir la página correspondiente.
                        if (Number(notification.leida)) {

                            if (
                                notification.tipo === 'solicitud_cobro'
                                && notification.solicitud_cobro_id
                            ) {
                                window.location.href =
                                    '<?= e(app_url('solicitudes-cobro')) ?>'
                                    + '?solicitud='
                                    + encodeURIComponent(notification.solicitud_cobro_id);
                            }

                            return;
                        }

                        try {

                            const response = await fetch(
                                '<?= e(app_url('/api/notificaciones/')) ?>'
                                + notification.id
                                + '/leer',
                                {
                                    method: 'POST',

                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },

                                    credentials: 'same-origin',

                                    body: new URLSearchParams({
                                        _token: '<?= e(csrf_token()) ?>'
                                    })
                                }
                            );

                            const data = await response.json();

                            if (!data.success) {

                                console.error(
                                    'No se pudo marcar la notificación como leída.',
                                    data
                                );

                                return;
                            }

                            notification.leida = 1;
                            item.classList.remove('notification-unread');
                            loadUnreadCount();

                            // Si la notificación corresponde a una solicitud de cobro,
                            // llevar al administrador a la página de solicitudes.
                            if (
                                notification.tipo === 'solicitud_cobro'
                                && notification.solicitud_cobro_id
                            ) {
                                window.location.href =
                                    '<?= e(app_url('solicitudes-cobro')) ?>'
                                    + '?solicitud='
                                    + encodeURIComponent(notification.solicitud_cobro_id);
                            }

                        } catch (error) {

                            console.error(
                                'Error al marcar la notificación como leída:',
                                error
                            );
                        }
                    });
                });

            } catch (error) {

                console.error(
                    'Error al cargar notificaciones:',
                    error
                );

                notificationList.innerHTML = `
                    <div class="text-center text-danger py-4">
                        Error al cargar las notificaciones.
                    </div>
                `;
            }
        }


        /*
        * Convierte la fecha de la notificación
        * a un formato más amigable.
        */
        function formatNotificationDate(dateString) {

            if (!dateString) {
                return '';
            }

            const date = new Date(
                dateString.replace(' ', 'T')
            );

            if (Number.isNaN(date.getTime())) {
                return dateString;
            }

            return date.toLocaleString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }


        /*
        * Evita insertar directamente contenido
        * proveniente de la base de datos como HTML.
        */
        function escapeHtml(value) {

            const div = document.createElement('div');

            div.textContent = value ?? '';

            return div.innerHTML;
        }


        /*
        * Cuando se abre la campana,
        * cargamos las notificaciones.
        */
        const notificationButton =
            document.getElementById('notificationButton');

        if (notificationButton) {

            notificationButton.addEventListener(
                'click',
                loadNotifications
            );
        }

        marcarNotificacionPushComoLeida();

        /*
        * Cargar el contador al entrar a cualquier página.
        */
        loadUnreadCount();

        /*
        * Actualizar el contador periódicamente.
        *
        * Por ahora cada 30 segundos.
        */
        setInterval(
            loadUnreadCount,
            30000
        );

    });
    </script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>