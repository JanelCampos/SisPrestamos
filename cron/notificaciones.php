<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/Repositories.php';
require __DIR__ . '/../app/Services.php';

use App\NotificationService;

$notificationService = new NotificationService();

$delete = $notificationService->borrarNotificacion();

$result = $notificationService->queueUpcoming();

// $result = $notificationService->cambiarEstadoCuota();

$notificationService->calcularMora();

// echo "Notificaciones programadas: {$result}" . PHP_EOL;
header('Location: ../public/usuarios');
exit;