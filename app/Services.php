<?php

namespace App;

use DateInterval;
use DateTimeImmutable;

class AuthService
{
    private AuthRepository $repository;

    public function __construct()
    {
        $this->repository = new AuthRepository();
    }

    public function attempt(string $login, string $password): bool
    {
        $user = $this->repository->findByLogin($login);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        Auth::login($user);

        return true;
    }
}

class DashboardService
{
    private DashboardRepository $repository;

    public function __construct()
    {
        $this->repository = new DashboardRepository();
    }

    public function overview(): array
    {
        return [
            'metrics' => $this->repository->metrics(),
            'chart' => $this->repository->monthlyChartData(),
            'alerts' => $this->repository->alerts(),
        ];
    }
}

class ClientService
{
    private ClientRepository $repository;

    public function __construct()
    {
        $this->repository = new ClientRepository();
    }

    public function all(array $filters = []): array
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function search(string $term): array
    {
        return $this->repository->search($term);
    }

    public function save(array $data, ?int $id = null): int
    {
        $required = [
            'nombres', 'dni', 'telefono', 'direccion', 'nacionalidad',
            'tipo_vivienda', 'situacion_laboral', 'estado_civil',
            'garante_nombre_completo', 'garante_dni', 'garante_telefono', 'garante_direccion',
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException('Todos los campos obligatorios del cliente y garante deben completarse.');
            }
        }

        if (!preg_match('/^[0-9A-Za-z\-]{6,20}$/', $data['dni'])) {
            throw new \InvalidArgumentException('El DNI del cliente no tiene un formato valido.');
        }

        if (!preg_match('/^[0-9A-Za-z\-]{6,20}$/', $data['garante_dni'])) {
            throw new \InvalidArgumentException('El DNI del garante no tiene un formato valido.');
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El correo electronico del cliente no es valido.');
        }

        return $this->repository->save($data, $id);
    }

    public function deactivate(int $id): void
    {
        $this->repository->deactivate($id);
    }
}

class LoanService
{
    private LoanRepository $repository;

    public function __construct()
    {
        $this->repository = new LoanRepository();
    }

    public function all(array $filters = []): array
    {
        return $this->repository->all($filters);
    }

    public function products(): array
    {
        return $this->repository->activeProducts();
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function simulate(array $payload): array
    {
        $this->validateLoanPayload($payload, false);

        $principal = (float) $payload['monto_principal'];
        $installments = (int) $payload['plazo_cuotas'];
        $periodicRate = $this->periodicRate(
            $payload['tasa_interes_tipo'],
            (float) $payload['tasa_interes_valor'],
            $payload['frecuencia_pago']
        );
        $firstDueDate = new DateTimeImmutable($payload['fecha_primer_pago']);

        if ($periodicRate > 0) {
            $quotaAmount = ($principal * $periodicRate) / (1 - pow(1 + $periodicRate, -$installments));
        } else {
            $quotaAmount = $principal / max(1, $installments);
        }

        $balance = $principal;
        $schedule = [];

        for ($index = 1; $index <= $installments; $index++) {
            $interest = round($balance * $periodicRate, 2);
            $capital = round($quotaAmount - $interest, 2);

            if ($index === $installments) {
                $capital = round($balance, 2);
                $quotaAmount = round($capital + $interest, 2);
            }

            $balance = round($balance - $capital, 2);

            $schedule[] = [
                'numero_cuota' => $index,
                'fecha_vencimiento' => $this->nextDueDate($firstDueDate, $payload['frecuencia_pago'], $index - 1)->format('Y-m-d'),
                'capital_programado' => $capital,
                'interes_programado' => $interest,
                'monto_programado' => round($quotaAmount, 2),
            ];
        }

        return [
            'resumen' => [
                'monto_principal' => $principal,
                'monto_cuota' => round($quotaAmount, 2),
                'total_interes' => round(array_sum(array_column($schedule, 'interes_programado')), 2),
                'total_pagar' => round(array_sum(array_column($schedule, 'monto_programado')), 2),
            ],
            'cronograma' => $schedule,
        ];
    }

    public function create(array $payload, int $userId): int
    {
        $this->validateLoanPayload($payload, true);

        if ($this->repository->hasBlockingDebt((int) $payload['cliente_id'])) {
            throw new \InvalidArgumentException('El cliente ya tiene un prestamo vigente, vencido o moroso.');
        }

        $simulation = $this->simulate($payload);

        return $this->repository->create($payload, $simulation['cronograma'], $userId);
    }

    public function recordPayment(int $loanId, array $payload, int $userId): string
    {
        if (empty($payload['monto_recibido']) || (float) $payload['monto_recibido'] <= 0) {
            throw new \InvalidArgumentException('El monto recibido debe ser mayor que cero.');
        }

        if (empty($payload['metodo_pago'])) {
            throw new \InvalidArgumentException('Selecciona un metodo de pago.');
        }

        return $this->repository->recordPayment($loanId, $payload, $userId);
    }

    public function requestPaymentAuthorization(
        int $loanId,
        array $payload,
        int $userId
    ): void {
        if (
            empty($payload['monto_recibido'])
            || (float) $payload['monto_recibido'] <= 0
        ) {
            throw new \InvalidArgumentException(
                'El monto recibido debe ser mayor que cero.'
            );
        }

        if (empty($payload['metodo_pago'])) {
            throw new \InvalidArgumentException(
                'Selecciona un metodo de pago.'
            );
        }

        $connection = Database::connection();

        $connection->beginTransaction();

        try {

            $solicitudCobroId =
                $this->repository->requestPaymentAuthorization(
                    $loanId,
                    $payload,
                    $userId
                );

            $notificationUserService =
                new NotificationUserService();

            $notificationUserService->notifyAdminNewPaymentRequest(
                $loanId,
                $solicitudCobroId,
                $userId,
                (float) $payload['monto_recibido']
            );

            $connection->commit();

        } catch (\Throwable $throwable) {

            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }

        /*
        * Enviamos el Push después del COMMIT.
        * Si Web Push falla, la solicitud de cobro
        * y la notificación interna no se pierden.
        */
        try {

            $administratorIds =
                $notificationUserService->getActiveAdministratorIds();

            $pushNotificationService =
                new PushNotificationService();

            foreach ($administratorIds as $administratorId) {

                $pushNotificationService->sendToUser(
                    (int) $administratorId,
                    'Nueva solicitud de cobro',
                    'Se ha recibido una nueva solicitud de cobro por S/ '
                    . number_format(
                        (float) $payload['monto_recibido'],
                        2,
                        '.',
                        ''
                    ),
                    app_url('solicitudes-cobro')
                    . '?solicitud='
                    . $solicitudCobroId
                );
            }

        } catch (\Throwable $throwable) {

            error_log(
                'Error al enviar Push de nueva solicitud de cobro: '
                . $throwable->getMessage()
            );
        }
    }

    public function approvePaymentRequest(
        int $requestId,
        int $approverId
    ): void {
        $connection = Database::connection();

        $connection->beginTransaction();

        try {

            // Obtener el cobrador que realizó la solicitud
            $statement = $connection->prepare(
                'SELECT usuario_solicitante_id
                FROM solicitudes_cobro
                WHERE id = :id
                AND estado = "pendiente"
                LIMIT 1'
            );

            $statement->execute([
                'id' => $requestId,
            ]);

            $request = $statement->fetch(\PDO::FETCH_ASSOC);

            if (!$request) {
                throw new \RuntimeException(
                    'La solicitud no existe o ya fue procesada.'
                );
            }

            $notificationUserService =
                new NotificationUserService();

            // Aprobar la solicitud
            $this->repository->approvePaymentRequest(
                $requestId,
                $approverId
            );

            // Crear notificación para el cobrador
            $notificationId =
                $notificationUserService
                    ->notifyPaymentRequestApproved(
                        $requestId
                    );

            $connection->commit();

        } catch (\Throwable $throwable) {

            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }

        /*
        * El Push se envía después del COMMIT.
        * Si Push falla, la aprobación no se pierde.
        */
        try {

            $pushNotificationService =
                new PushNotificationService();

            $pushNotificationService->sendToUser(
                (int) $request['usuario_solicitante_id'],
                'Solicitud de cobro aprobada',
                'Tu solicitud de cobro ha sido aprobada.',
                app_url('solicitudes-cobro')
                . '?solicitud='
                . $requestId,
                $notificationId
            );

        } catch (\Throwable $throwable) {

            error_log(
                'Error al enviar Push de solicitud aprobada: '
                . $throwable->getMessage()
            );
        }
    }

    public function rejectPaymentRequest(
        int $requestId,
        int $approverId
    ): void {
        $connection = Database::connection();

        $connection->beginTransaction();

        try {

            // Obtener el cobrador que realizó la solicitud
            $statement = $connection->prepare(
                'SELECT usuario_solicitante_id
                FROM solicitudes_cobro
                WHERE id = :id
                AND estado = "pendiente"
                LIMIT 1'
            );

            $statement->execute([
                'id' => $requestId,
            ]);

            $request = $statement->fetch(\PDO::FETCH_ASSOC);

            if (!$request) {
                throw new \RuntimeException(
                    'La solicitud no existe o ya fue procesada.'
                );
            }

            $notificationUserService =
                new NotificationUserService();

            // Rechazar la solicitud
            $this->repository->rejectPaymentRequest(
                $requestId,
                $approverId
            );

            // Crear notificación para el cobrador
            $notificationId =
                $notificationUserService
                    ->notifyPaymentRequestRejected(
                        $requestId
                    );

            $connection->commit();

        } catch (\Throwable $throwable) {

            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }

        /*
        * El Push se envía después del COMMIT.
        */
        try {

            $pushNotificationService =
                new PushNotificationService();

            $pushNotificationService->sendToUser(
                (int) $request['usuario_solicitante_id'],
                'Solicitud de cobro rechazada',
                'Tu solicitud de cobro ha sido rechazada.',
                app_url('solicitudes-cobro')
                . '?solicitud='
                . $requestId,
                $notificationId
            );

        } catch (\Throwable $throwable) {

            error_log(
                'Error al enviar Push de solicitud rechazada: '
                . $throwable->getMessage()
            );
        }
    }

    public function exportReport(string $type, array $filters = []): array
    {
        $rows = $type === 'cobros'
            ? $this->repository->collectionReport($filters)
            : $this->repository->portfolioReport($filters);

        return ['rows' => $rows, 'type' => $type];
    }

    private function validateLoanPayload(array $payload, bool $requireClient): void
    {
        $required = [
            'monto_principal', 'tasa_interes_tipo', 'tasa_interes_valor',
            'tasa_mora_diaria', 'fecha_otorgamiento', 'fecha_primer_pago',
            'plazo_cuotas', 'frecuencia_pago', 'producto_id',
        ];

        if ($requireClient) {
            $required[] = 'cliente_id';
        }

        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                throw new \InvalidArgumentException('Completa todos los datos obligatorios del prestamo.');
            }
        }

        if ((float) $payload['monto_principal'] <= 0) {
            throw new \InvalidArgumentException('El monto principal debe ser mayor a cero.');
        }

        if ((int) $payload['plazo_cuotas'] <= 0) {
            throw new \InvalidArgumentException('El plazo de cuotas debe ser mayor a cero.');
        }

        if (!in_array($payload['tasa_interes_tipo'], ['anual', 'mensual'], true)) {
            throw new \InvalidArgumentException('El tipo de tasa no es valido.');
        }

        if (!in_array($payload['frecuencia_pago'], ['diario', '10 dias', 'semanal', 'quincenal', 'mensual'], true)) {
            throw new \InvalidArgumentException('La frecuencia de pago no es valida.');
        }
    }

    private function periodicRate(string $rateType, float $rateValue, string $frequency): float
    {
        $monthlyRate = $rateType === 'anual'
            ? ($rateValue / 12) / 100
            : $rateValue / 100;

        switch ($frequency) {
            case 'diario':
                return $monthlyRate * (1 / 30);

            case '10 dias':
                return $monthlyRate * (10 / 30);

            case 'semanal':
                return $monthlyRate * (7 / 30);

            case 'quincenal':
                return $monthlyRate * (15 / 30);

            case 'mensual':
            default:
                return $monthlyRate;
        }
    }

    private function nextDueDate(DateTimeImmutable $firstDueDate, string $frequency, int $offset): DateTimeImmutable
    {
        if ($offset === 0) {
            return $firstDueDate;
        }

        switch ($frequency) {
            case 'diario':
                return $firstDueDate->add(new DateInterval('P' . $offset . 'D'));
            case '10 dias':
                return $firstDueDate->add(new DateInterval('P' . ($offset * 10) . 'D'));
            case 'semanal':
                return $firstDueDate->add(new DateInterval('P' . ($offset * 7) . 'D'));
            case 'quincenal':
                return $firstDueDate->add(new DateInterval('P' . ($offset * 15) . 'D'));
            case 'mensual':
            default:
                return $firstDueDate->add(new DateInterval('P' . $offset . 'M'));
        }
    }
}

class ReportService
{
    private LoanService $loanService;

    public function __construct()
    {
        $this->loanService = new LoanService();
    }

    public function export(string $format, string $type, array $filters = []): void
    {
        $payload = $this->loanService->exportReport($type, $filters);

        if ($format === 'excel') {
            $this->exportExcel($payload['rows'], $type);

            return;
        }

        $this->exportHtmlAsPdf($payload['rows'], $type);
    }

    private function exportExcel(array $rows, string $type): void
    {
        $filename = $type . '-' . date('Ymd_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo '<!DOCTYPE html>';
        echo '<html lang="es">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<style>';

        echo 'body {
            font-family: Arial, sans-serif;
        }';

        echo 'table {
            border-collapse: collapse;
            width: 100%;
        }';

        echo 'th {
            background-color: #198754;
            color: white;
            font-weight: bold;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }';

        echo 'td {
            border: 1px solid #ccc;
            padding: 7px;
        }';

        echo '.titulo {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 12px;
        }';

        echo '.fecha {
            text-align: right;
            color: #666;
            padding: 8px;
        }';

        echo '</style>';
        echo '</head>';

        echo '<body>';

        $titulo = $type === 'cobros'
            ? 'REPORTE DE COBROS'
            : 'REPORTE DE CARTERA';

        /*
        * Título
        */
        echo '<table>';

        echo '<tr>';
        echo '<td colspan="' . (!empty($rows) ? count($rows[0]) : 1) . '" class="titulo">';
        echo htmlspecialchars($titulo);
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="' . (!empty($rows) ? count($rows[0]) : 1) . '" class="fecha">';
        echo 'Generado: ' . date('d/m/Y H:i:s');
        echo '</td>';
        echo '</tr>';

        echo '</table>';

        echo '<br>';

        /*
        * Datos
        */
        echo '<table>';

        if (!empty($rows)) {

            /*
            * Encabezados
            */
            echo '<tr>';

            foreach (array_keys($rows[0]) as $header) {

                $nombre = ucwords(
                    str_replace('_', ' ', $header)
                );

                echo '<th>';
                echo htmlspecialchars($nombre);
                echo '</th>';
            }

            echo '</tr>';

            /*
            * Filas
            */
            foreach ($rows as $row) {

                echo '<tr>';

                foreach ($row as $value) {

                    echo '<td>';
                    echo htmlspecialchars((string) $value);
                    echo '</td>';
                }

                echo '</tr>';
            }

        } else {

            echo '<tr>';
            echo '<td>No hay datos para exportar</td>';
            echo '</tr>';
        }

        echo '</table>';

        echo '</body>';
        echo '</html>';

        exit;
    }

    private function exportHtmlAsPdf(array $rows, string $type): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Reporte ' . e($type) . '</title>';
        echo '<style>body{font-family:Arial,sans-serif;padding:24px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px;font-size:12px}th{background:#f0f0f0}</style>';
        echo '</head><body>';
        echo '<h1>Reporte ' . e(ucfirst($type)) . '</h1>';
        echo '<table><thead><tr>';
        if ($rows !== []) {
            foreach (array_keys($rows[0]) as $heading) {
                echo '<th>' . e((string) $heading) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $value) {
                    echo '<td>' . e((string) $value) . '</td>';
                }
                echo '</tr>';
            }
        } else {
            echo '<th>Resultado</th></tr></thead><tbody><tr><td>Sin datos disponibles.</td></tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }
}

class NotificationService
{
    private NotificationRepository $repository;

    public function __construct()
    {
        $this->repository = new NotificationRepository();
    }

    public function queueUpcoming(): int
    {
        return $this->repository->queueUpcoming();
    }

    public function pending(): array
    {
        return $this->repository->pending();
    }

    public function borrarNotificacion(){
        return $this->repository->borrarNotificacion();
    }

    public function calcularMora(){
        return $this->repository->calcularMora();
    }

    public function cambiarEstadoCuota(){
        return $this->repository->cambiarEstadoCuota();
    }
}

class NotificationUserService
{
    private NotificationUserRepository $repository;

    public function __construct()
    {
        $this->repository = new NotificationUserRepository();
    }

    public function notifyAdminNewPaymentRequest(
        int $prestamoId,
        int $solicitudCobroId,
        int $usuarioSolicitanteId,
        float $monto
    ): int {
        return $this->repository->notifyAdminNewPaymentRequest(
            $prestamoId,
            $solicitudCobroId,
            $usuarioSolicitanteId,
            $monto
        );
    }

    public function getForUser(int $userId): array
    {
        return $this->repository->getForUser($userId);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->repository->getUnreadCount($userId);
    }

    public function markAsRead(
        int $notificationId,
        int $userId
    ): bool {
        return $this->repository->markAsRead(
            $notificationId,
            $userId
        );
    }

    public function getActiveAdministratorIds(): array
    {
        return $this->repository->getActiveAdministratorIds();
    }

    public function notifyPaymentRequestApproved(
        int $solicitudCobroId
    ): int {
        return $this->repository->notifyPaymentRequestApproved(
            $solicitudCobroId
        );
    }

    public function notifyPaymentRequestRejected(
        int $solicitudCobroId
    ): int {
        return $this->repository->notifyPaymentRequestRejected(
            $solicitudCobroId
        );
    }
}

class PushSubscriptionService
{
    private PushSubscriptionRepository $repository;

    public function __construct()
    {
        $this->repository = new PushSubscriptionRepository();
    }

    public function save(
        int $userId,
        string $endpoint,
        string $p256dh,
        string $auth
    ): void {
        $this->repository->save(
            $userId,
            $endpoint,
            $p256dh,
            $auth
        );
    }

    public function deleteByEndpoint(string $endpoint): bool
    {
        return $this->repository->deleteByEndpoint(
            $endpoint
        );
    }

    public function getByUserId(int $userId): array
    {
        return $this->repository->getByUserId($userId);
    }

    public function deleteById(int $id): bool
    {
        return $this->repository->deleteById($id);
    }
}

class PushNotificationService
{
    private PushSubscriptionRepository $subscriptionRepository;

    public function __construct()
    {
        $this->subscriptionRepository =
            new PushSubscriptionRepository();
    }

    /**
     * Envía una notificación Push a todos los dispositivos
     * registrados de un usuario.
     */
    public function sendToUser(
        int $userId,
        string $title,
        string $message,
        ?string $url = null,
        ?int $notificationId = null
    ): void {
        $subscriptions =
            $this->subscriptionRepository->getByUserId($userId);

        if (empty($subscriptions)) {
            return;
        }

        $config = require __DIR__ . '/../config/push.php';

        $auth = [
            'VAPID' => [
                'subject' => $config['vapid']['subject'],
                'publicKey' => $config['vapid']['publicKey'],
                'privateKey' => $config['vapid']['privateKey'],
            ],
        ];

        $webPush = new \Minishlink\WebPush\WebPush($auth);

        foreach ($subscriptions as $subscription) {

            $payload = json_encode([
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'notificationId' => $notificationId,
            ], JSON_UNESCAPED_UNICODE);

            $pushSubscription =
                \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $subscription['endpoint'],
                    'publicKey' => $subscription['p256dh'],
                    'authToken' => $subscription['auth'],
                ]);

            $webPush->queueNotification(
                $pushSubscription,
                $payload
            );
        }

        foreach ($webPush->flush() as $report) {

            if (!$report->isSuccess()) {

                // Por ahora solamente dejamos registrado
                // el error para poder identificarlo.
                error_log(
                    'Error Web Push: ' .
                    $report->getReason()
                );
            }
        }
    }
}
