<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/Auth.php';
require __DIR__ . '/../app/Repositories.php';
require __DIR__ . '/../app/Services.php';
require __DIR__ . '/../app/Controllers.php';

use App\AdminController;
use App\AuthController;
use App\ClientController;
use App\DashboardController;
use App\LoanController;
use App\ReportController;
use App\NotificationController;

$path = current_path();
$method = request_method();

if ($path === '/' && !\App\Auth::check()) {
    redirect_to('login');
}

if ($path === '/' && \App\Auth::check()) {
    redirect_to('dashboard');
}

switch (true) {
    case $path === '/login' && $method === 'GET':
        AuthController::showLogin();
        break;
    case $path === '/login' && $method === 'POST':
        AuthController::login();
        break;
    case $path === '/logout' && $method === 'POST':
        AuthController::logout();
        break;
    case $path === '/dashboard' && $method === 'GET':
        DashboardController::index();
        break;
    case $path === '/clientes' && $method === 'GET':
        ClientController::index();
        break;
    case $path === '/clientes/crear' && $method === 'GET':
        ClientController::create();
        break;
    case $path === '/clientes/crear' && $method === 'POST':
        ClientController::store();
        break;
    case preg_match('#^/clientes/editar/(\d+)$#', $path, $matches) === 1 && $method === 'GET':
        ClientController::edit((int) $matches[1]);
        break;
    case preg_match('#^/clientes/editar/(\d+)$#', $path, $matches) === 1 && $method === 'POST':
        ClientController::update((int) $matches[1]);
        break;
    case preg_match('#^/clientes/ver/(\d+)$#', $path, $matches) === 1 && $method === 'GET':
        ClientController::show((int) $matches[1]);
        break;
    case preg_match('#^/clientes/eliminar/(\d+)$#', $path, $matches) === 1 && $method === 'POST':
        ClientController::destroy((int) $matches[1]);
        break;
    case $path === '/prestamos' && $method === 'GET':
        LoanController::index();
        break;
    case $path === '/prestamos/crear' && $method === 'GET':
        LoanController::create();
        break;
    case $path === '/prestamos/crear' && $method === 'POST':
        LoanController::store();
        break;
    case preg_match('#^/prestamos/ver/(\d+)$#', $path, $matches) === 1 && $method === 'GET':
        LoanController::show((int) $matches[1]);
        break;
    case preg_match('#^/prestamos/(\d+)/pago$#', $path, $matches) === 1 && $method === 'POST':
        LoanController::recordPayment((int) $matches[1]);
        break;
    case preg_match('#^/prestamos/(\d+)/solicitud-cobro$#', $path, $matches) === 1 && $method === 'POST':
        LoanController::requestPaymentAuthorization((int) $matches[1]);
        break;
    case $path === '/reportes' && $method === 'GET':
        ReportController::index();
        break;
    case preg_match('#^/reportes/(cartera|cobros)/(excel|pdf)$#', $path, $matches) === 1 && $method === 'GET':
        ReportController::export($matches[1], $matches[2]);
        break;
    case $path === '/usuarios' && $method === 'GET':
        AdminController::users();
        break;
    case $path === '/solicitudes-cobro' && $method === 'GET':
        AdminController::paymentRequests();
        break;
    case preg_match('#^/solicitudes-cobro/(\d+)/aprobar$#', $path, $matches) === 1 && $method === 'POST':
        AdminController::approvePaymentRequest((int) $matches[1]);
        break;
    case preg_match('#^/solicitudes-cobro/(\d+)/rechazar$#', $path, $matches) === 1 && $method === 'POST':
        AdminController::rejectPaymentRequest((int) $matches[1]);
        break;
    case $path === '/configuracion' && $method === 'GET':
        AdminController::settings();
        break;
    case $path === '/api/clientes/buscar' && $method === 'GET':
        ClientController::searchApi();
        break;
    case $path === '/api/prestamos/simular' && $method === 'POST':
        LoanController::simulateApi();
        break;
    case $path === '/api/notificaciones' && $method === 'GET':
        NotificationController::index();
        break;
    case $path === '/api/notificaciones/no-leidas' && $method === 'GET':
        NotificationController::unreadCount();
        break;
    default:
        abort(404, 'La ruta solicitada no existe.');
}
