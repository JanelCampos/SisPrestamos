<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/Repositories.php';
require __DIR__ . '/../app/Services.php';

use App\NotificationService;

header('Content-Type: application/json; charset=utf-8');

try {

    $notificationService = new NotificationService();

    $result = $notificationService->queueUpcoming();

    echo json_encode([
        'ok' => true,
        'cantidad' => $result,
        'mensaje' => "Notificaciones programadas: {$result}"
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}