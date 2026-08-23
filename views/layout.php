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

        /* En celular el sidebar funciona como menú lateral */
        @media (max-width: 991.98px) {

            .mobile-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                height: 100vh;
                z-index: 1050;

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
                z-index: 1040;
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


                        <form
                            method="post"
                            action="<?= e(app_url('logout')) ?>">

                            <?= csrf_field() ?>

                            <button
                                type="submit"
                                class="btn btn-outline-danger btn-sm">

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

</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>