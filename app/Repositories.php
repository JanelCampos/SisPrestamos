<?php

namespace App;

use PDO;

class AuthRepository
{
    public function findByLogin(string $login): ?array
    {
        $sql = 'SELECT u.*, r.nombre AS role_name
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE (u.usuario = :login_user OR u.email = :login_email) AND u.estado = "activo"
                LIMIT 1';

        $statement = Database::connection()->prepare($sql);
        $statement->execute([
            'login_user' => $login,
            'login_email' => $login,
        ]);
        $user = $statement->fetch();

        return $user ?: null;
    }
}

class DashboardRepository
{
    public function metrics(): array
    {
        $sql = 'SELECT
                    COALESCE(SUM(monto_principal), 0) AS total_prestado,
                    0 AS utilidad_total,
                    0 AS utilidad_mes,
                    SUM(CASE WHEN estado = "vigente" THEN 1 ELSE 0 END) AS prestamos_vigentes,
                    SUM(CASE WHEN estado = "pagado" THEN 1 ELSE 0 END) AS prestamos_pagados,
                    SUM(CASE WHEN estado = "moroso" THEN 1 ELSE 0 END) AS prestamos_morosos
                FROM prestamos';

        $loanMetrics = Database::connection()->query($sql)->fetch() ?: [];
        $paymentMetrics = Database::connection()->query(
            'SELECT
                COALESCE(SUM(monto_interes), 0) AS utilidad_mes_intereses,
                COALESCE(SUM(monto_mora), 0) AS utilidad_mes_mora
             FROM pago_detalle_cuota
             WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE())'
        )->fetch() ?: [];

        $utilidadTotal = Database::connection()->query('
            SELECT 
                COALESCE(SUM(monto_interes), 0) + COALESCE(SUM(monto_mora), 0) AS utilidad_total
            FROM pago_detalle_cuota
        ')->fetch() ?: [];

        $loanMetrics['utilidad_mes'] = ($paymentMetrics['utilidad_mes_intereses'] ?? 0) + ($paymentMetrics['utilidad_mes_mora'] ?? 0);
        $loanMetrics['utilidad_total'] = $utilidadTotal['utilidad_total'];

        return $loanMetrics;
    }

    public function monthlyChartData(): array
    {
        $sql = 'SELECT
                    DATE_FORMAT(fecha_otorgamiento, "%Y-%m") AS periodo,
                    COUNT(*) AS prestamos_otorgados,
                    COALESCE(SUM(monto_principal), 0) AS monto_otorgado,
                    COALESCE(SUM(total_interes), 0) AS interes_proyectado,
                    COALESCE(AVG(CASE WHEN estado = "moroso" THEN 100 ELSE 0 END), 0) AS tasa_mora
                FROM prestamos
                WHERE fecha_otorgamiento >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY DATE_FORMAT(fecha_otorgamiento, "%Y-%m")
                ORDER BY periodo ASC';

        return Database::connection()->query($sql)->fetchAll();
    }

    public function alerts(): array
    {
        $sql = 'SELECT p.id, p.numero_prestamo, c.nombres AS cliente, cp.fecha_vencimiento, cp.mora_acumulada, cp.mora_pagada,
                cp.saldo_cuota, cp.estado
                FROM cuotas_prestamo cp
                INNER JOIN prestamos p ON p.id = cp.prestamo_id
                INNER JOIN clientes c ON c.id = p.cliente_id
                WHERE cp.estado IN ("vencida")
                ORDER BY cp.fecha_vencimiento ASC
                LIMIT 8';

        return Database::connection()->query($sql)->fetchAll();
    }
}

class ClientRepository
{
    public function all(array $filters = []): array
    {
        $sql = 'SELECT c.*, g.nombre_completo AS garante_nombre,
                    (
                        SELECT COALESCE(SUM(p.saldo_pendiente), 0)
                        FROM prestamos p
                        WHERE p.cliente_id = c.id 
                            AND p.estado IN ("vigente", "vencido", "moroso")
                    ) AS deuda_activa
                FROM clientes c
                LEFT JOIN garantes g ON g.cliente_id = c.id
                WHERE c.eliminado_en IS NULL';

        $params = [];

        if (!empty($filters['q'])) {
            $sql .= ' AND (
                c.nombres LIKE :search_nombres
                OR c.dni LIKE :search_dni
                OR c.telefono LIKE :search_telefono
            )';

            $search = '%' . $filters['q'] . '%';

            $params['search_nombres'] = $search;
            $params['search_dni'] = $search;
            $params['search_telefono'] = $search;
        }

        $sql .= ' ORDER BY c.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function search(string $term): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, nombres, dni
             FROM clientes
             WHERE eliminado_en IS NULL AND (nombres LIKE :term OR dni LIKE :term)
             ORDER BY nombres ASC
             LIMIT 15'
        );
        $statement->execute(['term' => '%' . $term . '%']);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT c.*, g.id AS garante_id, g.foto AS garante_foto, g.nombre_completo AS garante_nombre_completo,
                    g.dni AS garante_dni, g.telefono AS garante_telefono, g.direccion AS garante_direccion
             FROM clientes c
             LEFT JOIN garantes g ON g.cliente_id = c.id
             WHERE c.id = :id AND c.eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $client = $statement->fetch();

        return $client ?: null;
    }

    public function save(array $data, ?int $id = null): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();
        var_dump($data);
        var_dump($id);

        try {
            if ($id === null) {
                $statement = $connection->prepare(
                    'INSERT INTO clientes
                    (codigo, foto, nombres, dni, telefono, email, direccion, nacionalidad, tipo_vivienda, situacion_laboral, estado_civil, direccion_trabajo, creado_en)
                    VALUES (:codigo, :foto, :nombres, :dni, :telefono, :email, :direccion, :nacionalidad, :tipo_vivienda, :situacion_laboral, :estado_civil, :direccion_trabajo, NOW())'
                );
                $statement->execute($this->clientPayload($data));
                $clientId = (int) $connection->lastInsertId();
            } else {
                $payload = $this->clientPayload($data);
                $payload['id'] = $id;
                $statement = $connection->prepare(
                    'UPDATE clientes SET
                        foto = :foto,
                        nombres = :nombres,
                        dni = :dni,
                        telefono = :telefono,
                        email = :email,
                        direccion = :direccion,
                        nacionalidad = :nacionalidad,
                        tipo_vivienda = :tipo_vivienda,
                        situacion_laboral = :situacion_laboral,
                        estado_civil = :estado_civil,
                        direccion_trabajo = :direccion_trabajo
                     WHERE id = :id'
                );
                unset($payload['codigo']);
                $statement->execute($payload);
                $clientId = $id;
            }

            $guarantor = [
                'cliente_id' => $clientId,
                'foto' => $data['garante_foto'],
                'nombre_completo' => $data['garante_nombre_completo'],
                'dni' => $data['garante_dni'],
                'telefono' => $data['garante_telefono'],
                'direccion' => $data['garante_direccion'],
            ];

            $exists = $connection->prepare('SELECT id FROM garantes WHERE cliente_id = :cliente_id LIMIT 1');
            $exists->execute(['cliente_id' => $clientId]);
            $guarantorId = $exists->fetchColumn();

            if ($guarantorId) {
                $guarantor['id'] = $guarantorId;
                unset($guarantor['cliente_id']);
                
                $statement = $connection->prepare(
                    'UPDATE garantes SET
                        foto = :foto,
                        nombre_completo = :nombre_completo,
                        dni = :dni,
                        telefono = :telefono,
                        direccion = :direccion
                     WHERE id = :id'
                );
            } else {
                $statement = $connection->prepare(
                    'INSERT INTO garantes (cliente_id, foto, nombre_completo, dni, telefono, direccion)
                     VALUES (:cliente_id, :foto, :nombre_completo, :dni, :telefono, :direccion)'
                );
            }

            $statement->execute($guarantor);
            $connection->commit();

            return $clientId;
        } catch (\Throwable $throwable) {
            $connection->rollBack();
            throw $throwable;
        }
    }

    public function deactivate(int $id): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE clientes SET eliminado_en = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    private function clientPayload(array $data): array
    {
        return [
            'codigo' => $data['codigo'] ?? 'CLI-' . date('YmdHis'),
            'foto' => $data['foto'],
            'nombres' => $data['nombres'],
            'dni' => $data['dni'],
            'telefono' => $data['telefono'],
            'email' => $data['email'] ?: null,
            'direccion' => $data['direccion'],
            'nacionalidad' => $data['nacionalidad'],
            'tipo_vivienda' => $data['tipo_vivienda'],
            'situacion_laboral' => $data['situacion_laboral'],
            'estado_civil' => $data['estado_civil'],
            'direccion_trabajo' => $data['direccion_trabajo'] ?: null,
        ];
    }
}

class LoanRepository
{
    public function all(array $filters = []): array
    {
        $sql = 'SELECT p.*, c.nombres AS cliente, u.nombre_completo AS usuario_registro
                FROM prestamos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                INNER JOIN usuarios u ON u.id = p.usuario_id
                WHERE 1 = 1';
        $params = [];

        if (!empty($filters['estado'])) {
            $sql .= ' AND p.estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['cliente_id'])) {
            $sql .= ' AND p.cliente_id = :cliente_id';
            $params['cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= ' AND p.fecha_otorgamiento >= :fecha_desde';
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= ' AND p.fecha_otorgamiento <= :fecha_hasta';
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        if (!empty($filters['vencimiento'])) {
            $sql .= ' AND EXISTS (
                            SELECT 1
                            FROM cuotas_prestamo cp
                            WHERE cp.prestamo_id = p.id
                              AND cp.fecha_vencimiento = :vencimiento
                        )';
            $params['vencimiento'] = $filters['vencimiento'];
        }

        $sql .= ' ORDER BY p.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function pendingPaymentRequests(): array
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'SELECT
                sc.id,
                sc.prestamo_id,
                sc.usuario_solicitante_id,
                sc.monto_recibido,
                sc.metodo_pago,
                sc.fecha_pago,
                sc.observacion,
                sc.estado,
                sc.creado_en,

                p.numero_prestamo,

                u.nombre_completo AS cobrador

            FROM solicitudes_cobro sc

            INNER JOIN prestamos p
                ON p.id = sc.prestamo_id

            INNER JOIN usuarios u
                ON u.id = sc.usuario_solicitante_id

            WHERE sc.estado = "pendiente"

            ORDER BY sc.creado_en ASC'
        );

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approvePaymentRequest(int $requestId, int $approverId): void
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'UPDATE solicitudes_cobro
            SET estado = "aprobada",
                usuario_aprobador_id = :usuario_aprobador_id,
                aprobado_en = NOW()
            WHERE id = :id
            AND estado = "pendiente"'
        );

        $statement->execute([
            'usuario_aprobador_id' => $approverId,
            'id' => $requestId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new \RuntimeException(
                'La solicitud no existe o ya fue procesada.'
            );
        }

        // Archivar la notificación relacionada
        $notificationStatement = $connection->prepare(
            'UPDATE notificaciones_usuario
            SET archivada = 1
            WHERE solicitud_cobro_id = :solicitud_cobro_id'
        );

        $notificationStatement->execute([
            'solicitud_cobro_id' => $requestId,
        ]);
    }

    public function rejectPaymentRequest(int $requestId, int $approverId): void
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'UPDATE solicitudes_cobro
            SET estado = "rechazada",
                usuario_aprobador_id = :usuario_aprobador_id,
                rechazado_en = NOW()
            WHERE id = :id
            AND estado = "pendiente"'
        );

        $statement->execute([
            'usuario_aprobador_id' => $approverId,
            'id' => $requestId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new \RuntimeException(
                'La solicitud no existe o ya fue procesada.'
            );
        }

        // Archivar la notificación relacionada
        $notificationStatement = $connection->prepare(
            'UPDATE notificaciones_usuario
            SET archivada = 1
            WHERE solicitud_cobro_id = :solicitud_cobro_id'
        );

        $notificationStatement->execute([
            'solicitud_cobro_id' => $requestId,
        ]);
    }

    public function latestPaymentRequest(int $loanId, int $userId): ?array
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'SELECT *
            FROM solicitudes_cobro
            WHERE prestamo_id = :prestamo_id
            AND usuario_solicitante_id = :usuario_id
            ORDER BY id DESC
            LIMIT 1'
        );

        $statement->execute([
            'prestamo_id' => $loanId,
            'usuario_id' => $userId,
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function hasBlockingDebt(int $clientId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM prestamos
             WHERE cliente_id = :cliente_id AND estado IN ("vigente", "vencido", "moroso")'
        );
        $statement->execute(['cliente_id' => $clientId]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function activeProducts(): array
    {
        return Database::connection()->query(
            'SELECT * FROM productos_prestamo WHERE estado = 1 ORDER BY nombre ASC'
        )->fetchAll();
    }

    public function create(array $loan, array $schedule, int $userId): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $number = 'PRE-' . date('YmdHis');
            $totalInterest = array_sum(array_column($schedule, 'interes_programado'));
            $statement = $connection->prepare(
                'INSERT INTO prestamos
                (cliente_id, producto_id, usuario_id, numero_prestamo, monto_principal, tasa_interes_tipo, tasa_interes_valor, tasa_mora_diaria, plazo_cuotas, frecuencia_pago, fecha_otorgamiento, fecha_primer_pago, total_interes, total_mora, saldo_pendiente, estado, observaciones, creado_en)
                VALUES
                (:cliente_id, :producto_id, :usuario_id, :numero_prestamo, :monto_principal, :tasa_interes_tipo, :tasa_interes_valor, :tasa_mora_diaria, :plazo_cuotas, :frecuencia_pago, :fecha_otorgamiento, :fecha_primer_pago, :total_interes, 0, :saldo_pendiente, "vigente", :observaciones, NOW())'
            );
            $statement->execute([
                'cliente_id' => $loan['cliente_id'],
                'producto_id' => $loan['producto_id'],
                'usuario_id' => $userId,
                'numero_prestamo' => $number,
                'monto_principal' => $loan['monto_principal'],
                'tasa_interes_tipo' => $loan['tasa_interes_tipo'],
                'tasa_interes_valor' => $loan['tasa_interes_valor'],
                'tasa_mora_diaria' => $loan['tasa_mora_diaria'],
                'plazo_cuotas' => $loan['plazo_cuotas'],
                'frecuencia_pago' => $loan['frecuencia_pago'],
                'fecha_otorgamiento' => $loan['fecha_otorgamiento'],
                'fecha_primer_pago' => $loan['fecha_primer_pago'],
                'total_interes' => $totalInterest,
                'saldo_pendiente' => $loan['monto_principal'] + $totalInterest,
                'observaciones' => $loan['observaciones'] ?: null,
            ]);

            $loanId = (int) $connection->lastInsertId();
            $quotaStatement = $connection->prepare(
                'INSERT INTO cuotas_prestamo
                (prestamo_id, numero_cuota, fecha_vencimiento, capital_programado, interes_programado, capital_pagado, interes_pagado, mora_acumulada, mora_pagada, monto_programado, saldo_cuota, fecha_ultimo_calculo_mora, estado)
                VALUES
                (:prestamo_id, :numero_cuota, :fecha_vencimiento, :capital_programado, :interes_programado, 0, 0, 0, 0, :monto_programado, :saldo_cuota, NULL, "pendiente")'
            );

            foreach ($schedule as $row) {
                $quotaStatement->execute([
                    'prestamo_id' => $loanId,
                    'numero_cuota' => $row['numero_cuota'],
                    'fecha_vencimiento' => $row['fecha_vencimiento'],
                    'capital_programado' => $row['capital_programado'],
                    'interes_programado' => $row['interes_programado'],
                    'monto_programado' => $row['monto_programado'],
                    'saldo_cuota' => $row['monto_programado'],
                ]);
            }

            $movement = $connection->prepare(
                'INSERT INTO movimientos_prestamo (prestamo_id, usuario_id, tipo_movimiento, descripcion, metadata, creado_en)
                 VALUES (:prestamo_id, :usuario_id, :tipo_movimiento, :descripcion, :metadata, NOW())'
            );
            $movement->execute([
                'prestamo_id' => $loanId,
                'usuario_id' => $userId,
                'tipo_movimiento' => 'desembolso',
                'descripcion' => 'Prestamo registrado y cronograma generado automaticamente.',
                'metadata' => json_encode(['cuotas' => count($schedule)]),
            ]);

            $connection->commit();

            return $loanId;
        } catch (\Throwable $throwable) {
            $connection->rollBack();
            throw $throwable;
        }
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.*, c.nombres AS cliente, c.dni, c.telefono, c.direccion,
                    u.nombre_completo AS usuario_registro, pr.nombre AS producto
             FROM prestamos p
             INNER JOIN clientes c ON c.id = p.cliente_id
             INNER JOIN usuarios u ON u.id = p.usuario_id
             INNER JOIN productos_prestamo pr ON pr.id = p.producto_id
             WHERE p.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $loan = $statement->fetch();

        if (!$loan) {
            return null;
        }

        $loan['cuotas'] = $this->installments($id);
        $loan['pagos'] = $this->payments($id);
        $loan['movimientos'] = $this->movements($id);

        return $loan;
    }

    public function installments(int $loanId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT *
             FROM cuotas_prestamo
             WHERE prestamo_id = :prestamo_id
             ORDER BY numero_cuota ASC'
        );
        $statement->execute(['prestamo_id' => $loanId]);

        return $statement->fetchAll();
    }

    public function payments(int $loanId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.*, u.nombre_completo AS usuario
             FROM pagos p
             INNER JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.prestamo_id = :prestamo_id
             ORDER BY p.fecha_pago DESC'
        );
        $statement->execute(['prestamo_id' => $loanId]);

        return $statement->fetchAll();
    }

    public function movements(int $loanId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT mp.*, u.nombre_completo AS usuario
             FROM movimientos_prestamo mp
             INNER JOIN usuarios u ON u.id = mp.usuario_id
             WHERE mp.prestamo_id = :prestamo_id
             ORDER BY mp.id DESC'
        );
        $statement->execute(['prestamo_id' => $loanId]);

        return $statement->fetchAll();
    }

    public function recordPayment(int $loanId, array $data, int $userId): string
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $loanStatement = $connection->prepare('SELECT * FROM prestamos WHERE id = :id LIMIT 1 FOR UPDATE');
            $loanStatement->execute(['id' => $loanId]);
            $loan = $loanStatement->fetch();

            if ($loan['estado'] === 'pagado') {
                throw new \RuntimeException(
                    'El préstamo ya está pagado completamente.'
                );
            }

            if (!$loan) {
                throw new \RuntimeException('El prestamo no existe.');
            }

            $userStatement = $connection->prepare(
                'SELECT 
                    u.id,
                    r.nombre AS role_name
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.id = :id
                LIMIT 1'
            );

            $userStatement->execute([
                'id' => $userId,
            ]);

            $user = $userStatement->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new \RuntimeException('El usuario no existe.');
            }

            $isCobrador = $user['role_name'] === 'cobrador';

            $paymentRequest = null;

            $paymentAmount = (float) $data['monto_recibido'];

            if ($isCobrador) {

                $paymentRequestStatement = $connection->prepare(
                    'SELECT *
                    FROM solicitudes_cobro
                    WHERE prestamo_id = :prestamo_id
                    AND usuario_solicitante_id = :usuario_id
                    AND estado = "aprobada"
                    ORDER BY id DESC
                    LIMIT 1
                    FOR UPDATE'
                );

                $paymentRequestStatement->execute([
                    'prestamo_id' => $loanId,
                    'usuario_id' => $userId,
                ]);

                $paymentRequest = $paymentRequestStatement->fetch(PDO::FETCH_ASSOC);

                if (!$paymentRequest) {
                    throw new \RuntimeException(
                        'No existe una solicitud de cobro aprobada para este prestamo.'
                    );
                }

                $requestedAmount = (float) $paymentRequest['monto_recibido'];

                if (abs($paymentAmount - $requestedAmount) > 0.009) {
                    throw new \RuntimeException(
                        'El monto del cobro no coincide con el monto aprobado en la solicitud.'
                    );
                }
            }

            $paymentAmount = (float) $data['monto_recibido'];
            $paymentDate = $data['fecha_pago'] ?: date('Y-m-d H:i:s');
            $receiptNumber = 'REC-' . date('YmdHis');

            $paymentStatement = $connection->prepare(
                'INSERT INTO pagos
                (prestamo_id, usuario_id, numero_recibo, fecha_pago, monto_recibido, metodo_pago, observacion, creado_en)
                VALUES
                (:prestamo_id, :usuario_id, :numero_recibo, :fecha_pago, :monto_recibido, :metodo_pago, :observacion, NOW())'
            );
            $paymentStatement->execute([
                'prestamo_id' => $loanId,
                'usuario_id' => $userId,
                'numero_recibo' => $receiptNumber,
                'fecha_pago' => $paymentDate,
                'monto_recibido' => $paymentAmount,
                'metodo_pago' => $data['metodo_pago'],
                'observacion' => $data['observacion'] ?: null,
            ]);

            $paymentId = (int) $connection->lastInsertId();

            if ($isCobrador && $paymentRequest) {
                $updateRequest = $connection->prepare(
                    'UPDATE solicitudes_cobro
                    SET estado = "utilizada",
                        pago_id = :pago_id,
                        utilizado_en = NOW()
                    WHERE id = :id
                    AND estado = "aprobada"'
                );

                $updateRequest->execute([
                    'pago_id' => $paymentId,
                    'id' => $paymentRequest['id'],
                ]);

                if ($updateRequest->rowCount() !== 1) {
                    throw new \RuntimeException(
                        'No se pudo marcar la solicitud de cobro como utilizada.'
                    );
                }
            }

            $detailStatement = $connection->prepare(
                'INSERT INTO pago_detalle_cuota
                (pago_id, cuota_id, monto_capital, monto_interes, monto_mora, creado_en)
                VALUES
                (:pago_id, :cuota_id, :monto_capital, :monto_interes, :monto_mora, NOW())'
            );

            $installments = $connection->prepare(
                'SELECT * FROM cuotas_prestamo WHERE prestamo_id = :prestamo_id ORDER BY numero_cuota ASC FOR UPDATE'
            );
            $installments->execute(['prestamo_id' => $loanId]);
            $rows = $installments->fetchAll();

            foreach ($rows as $row) {
                if ($paymentAmount <= 0) {
                    break;
                }

                // $lateFee = $this->calculateLateFeeRow($row, $loan['tasa_mora_diaria'], $paymentDate);
                $capitalDue = max(0, (float) $row['capital_programado'] - (float) $row['capital_pagado']);
                $interestDue = max(0, (float) $row['interes_programado'] - (float) $row['interes_pagado']);
                $lateDue = max( 0, (float) $row['mora_acumulada'] - (float) $row['mora_pagada'] );

                if (($capitalDue + $interestDue + $lateDue) <= 0) {
                    continue;
                }

                $moraApplied = min($paymentAmount, $lateDue);
                $paymentAmount -= $moraApplied;
                $interestApplied = min($paymentAmount, $interestDue);
                $paymentAmount -= $interestApplied;
                $capitalApplied = min($paymentAmount, $capitalDue);
                $paymentAmount -= $capitalApplied;

                $newCapitalPaid = (float) $row['capital_pagado'] + $capitalApplied;
                $newInterestPaid = (float) $row['interes_pagado'] + $interestApplied;
                $newLatePaid = (float) $row['mora_pagada'] + $moraApplied;
                $newBalance = max(0, (($capitalDue - $capitalApplied) + ($interestDue - $interestApplied)));
                $remainingMora = max( 0, (float) $row['mora_acumulada'] - $newLatePaid );
                $newStatus = 'pendiente';

                if ($newBalance <= 0.009 && $remainingMora <= 0.009) {
                    $newStatus = 'pagada';
                } elseif ($remainingMora > 0.009 || strtotime(substr($paymentDate, 0, 10)) > strtotime($row['fecha_vencimiento'])) {
                    $newStatus = 'vencida';
                } elseif (($capitalApplied + $interestApplied) > 0) {
                    $newStatus = 'parcial';
                }

                $updateQuota = $connection->prepare(
                    'UPDATE cuotas_prestamo
                     SET capital_pagado = :capital_pagado,
                         interes_pagado = :interes_pagado,
                         mora_pagada = :mora_pagada,
                         saldo_cuota = :saldo_cuota,
                         estado = :estado
                     WHERE id = :id'
                );
                $updateQuota->execute([
                    'capital_pagado' => $newCapitalPaid,
                    'interes_pagado' => $newInterestPaid,
                    'mora_pagada' => $newLatePaid,
                    'saldo_cuota' => $newBalance,
                    'estado' => $newStatus,
                    'id' => $row['id'],
                ]);

                if($newStatus === 'pagada'){
                    $deleteNotification = $connection->prepare(
                        'DELETE FROM notificaciones
                        WHERE cuota_id = :cuota_id'
                    );

                    $deleteNotification->execute([
                        'cuota_id' => $row['id'],
                    ]);
                }

                if (($capitalApplied + $interestApplied + $moraApplied) > 0) {
                    $detailStatement->execute([
                        'pago_id' => $paymentId,
                        'cuota_id' => $row['id'],
                        'monto_capital' => $capitalApplied,
                        'monto_interes' => $interestApplied,
                        'monto_mora' => $moraApplied,
                    ]);
                }
            }

            $summaryStatement = $connection->prepare(
                'SELECT
                    COALESCE(SUM(saldo_cuota), 0) AS saldo_total,
                    COALESCE(SUM(mora_acumulada - mora_pagada), 0) AS mora_total,
                    SUM(CASE WHEN estado = "vencida" OR (mora_acumulada - mora_pagada) > 0.009 THEN 1 ELSE 0 END) AS cuotas_en_mora,
                    SUM(CASE WHEN estado = "pagada" THEN 1 ELSE 0 END) AS cuotas_pagadas,
                    SUM(CASE WHEN estado != "pagada" THEN 1 ELSE 0 END) AS cuotas_pendientes,
                    SUM(CASE WHEN (mora_acumulada - mora_pagada) > 0.009 THEN 1 ELSE 0 END) AS cuotas_con_mora,
                    COUNT(*) AS cuotas_totales
                 FROM cuotas_prestamo
                 WHERE prestamo_id = :prestamo_id'
            );
            $summaryStatement->execute(['prestamo_id' => $loanId]);
            $summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);

            $saldoCuotas = (float) $summary['saldo_total']; 
            $moraTotal = (float) $summary['mora_total'];
            $saldoPendiente = $saldoCuotas + $moraTotal;

            $cuotasTotales = (int) $summary['cuotas_totales'];
            $cuotasPagadas = (int) $summary['cuotas_pagadas'];
            $cuotasEnMora = (int) $summary['cuotas_en_mora'];

            $loanStatus = 'vigente';
            if ($cuotasPagadas === $cuotasTotales &&  $moraTotal <= 0.009) {
                $loanStatus = 'pagado';
            } elseif($cuotasEnMora > 0){
                $loanStatus = 'moroso';
            }else{
                $loanStatus = 'vigente';
            }

            $updateLoan = $connection->prepare(
                'UPDATE prestamos
                 SET saldo_pendiente = :saldo_pendiente,
                     total_mora = :total_mora,
                     estado = :estado
                 WHERE id = :id'
            );
            $updateLoan->execute([
                'saldo_pendiente' => $saldoPendiente,
                'total_mora' => $moraTotal,
                'estado' => $loanStatus,
                'id' => $loanId,
            ]);

            $movement = $connection->prepare(
                'INSERT INTO movimientos_prestamo (prestamo_id, usuario_id, tipo_movimiento, descripcion, metadata, creado_en)
                 VALUES (:prestamo_id, :usuario_id, :tipo_movimiento, :descripcion, :metadata, NOW())'
            );
            $movement->execute([
                'prestamo_id' => $loanId,
                'usuario_id' => $userId,
                'tipo_movimiento' => 'pago',
                'descripcion' => 'Pago registrado en caja.',
                'metadata' => json_encode(['pago_id' => $paymentId, 'recibo' => $receiptNumber]),
            ]);

            $connection->commit();

            return $receiptNumber;
        } catch (\Throwable $throwable) {
            if ($connection->inTransaction()) { 
                $connection->rollBack(); 
            } 
            throw $throwable;
        }
    }

    public function requestPaymentAuthorization(
        int $loanId,
        array $data,
        int $userId
    ): int {
        $connection = Database::connection();

        // Verificar que el préstamo exista
        $loanStatement = $connection->prepare(
            'SELECT id
            FROM prestamos
            WHERE id = :id
            LIMIT 1'
        );

        $loanStatement->execute([
            'id' => $loanId,
        ]);

        if (!$loanStatement->fetch()) {
            throw new \RuntimeException('El prestamo no existe.');
        }

        $paymentDate = !empty($data['fecha_pago'])
            ? $data['fecha_pago']
            : date('Y-m-d H:i:s');

        $statement = $connection->prepare(
            'INSERT INTO solicitudes_cobro
            (
                prestamo_id,
                usuario_solicitante_id,
                monto_recibido,
                metodo_pago,
                fecha_pago,
                observacion,
                estado,
                creado_en
            )
            VALUES
            (
                :prestamo_id,
                :usuario_solicitante_id,
                :monto_recibido,
                :metodo_pago,
                :fecha_pago,
                :observacion,
                "pendiente",
                NOW()
            )'
        );

        $statement->execute([
            'prestamo_id' => $loanId,
            'usuario_solicitante_id' => $userId,
            'monto_recibido' => (float) $data['monto_recibido'],
            'metodo_pago' => $data['metodo_pago'],
            'fecha_pago' => $paymentDate,
            'observacion' => !empty($data['observacion'])
                ? $data['observacion']
                : null,
        ]);

        return (int) $connection->lastInsertId();
    }

    public function portfolioReport(array $filters = []): array
    {
        return $this->all($filters);
    }

    public function collectionReport(array $filters = []): array
    {
        $sql = 'SELECT p.numero_recibo, p.fecha_pago, p.monto_recibido, p.metodo_pago,
                       pr.numero_prestamo, c.nombres AS cliente, u.nombre_completo AS usuario
                FROM pagos p
                INNER JOIN prestamos pr ON pr.id = p.prestamo_id
                INNER JOIN clientes c ON c.id = pr.cliente_id
                INNER JOIN usuarios u ON u.id = p.usuario_id
                WHERE 1 = 1';
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $sql .= ' AND DATE(p.fecha_pago) >= :fecha_desde';
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= ' AND DATE(p.fecha_pago) <= :fecha_hasta';
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= ' ORDER BY p.fecha_pago DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function calculateLateFeeRow(array $installment, float $dailyRate, string $paymentDate): float
    {
        $dueDate = strtotime($installment['fecha_vencimiento']);
        $currentDate = strtotime(substr($paymentDate, 0, 10));
        $daysLate = (int) floor(($currentDate - $dueDate) / 86400);

        if ($daysLate <= 0) {
            return (float) $installment['mora_acumulada'];
        }

        $base = max(0, ((float) $installment['capital_programado'] - (float) $installment['capital_pagado']) + ((float) $installment['interes_programado'] - (float) $installment['interes_pagado']));
        $lateFee = $base * (($dailyRate / 100) * $daysLate);

        return round($lateFee, 2);
    }
}

class UserRepository
{
    public function all(): array
    {
        return Database::connection()->query(
            'SELECT u.id, u.nombre_completo, u.usuario, u.email, u.telefono, u.estado, r.nombre AS role_name
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             ORDER BY u.id ASC'
        )->fetchAll();
    }

    public function roles(): array
    {
        return Database::connection()->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();
    }
}

class NotificationRepository
{
    public function pending(): array
    {
        return Database::connection()->query(
            'SELECT n.*, c.nombres AS cliente, c.email, c.telefono
             FROM notificaciones n
             INNER JOIN clientes c ON c.id = n.cliente_id
             WHERE n.estado = "pendiente"
             ORDER BY n.fecha_programada ASC'
        )->fetchAll();
    }

   public function queueUpcoming(): int
    {

        $sql = 'INSERT INTO notificaciones
                (
                    prestamo_id,
                    cuota_id,
                    cliente_id,
                    canal,
                    destinatario,
                    asunto,
                    mensaje,
                    fecha_programada,
                    estado,
                    creado_en
                )
                SELECT DISTINCT
                    p.id,
                    cp.id,
                    c.id,
                    "email",
                    COALESCE(c.email, ""),
                    "Recordatorio de cuota",
                    CONCAT(
                        "Estimado/a ", c.nombres,
                        ", su cuota del prestamo ", p.numero_prestamo,
                        " vence el ", DATE_FORMAT(cp.fecha_vencimiento, "%d/%m/%Y")
                    ),
                    NOW(),
                    "pendiente",
                    NOW()
                FROM cuotas_prestamo cp
                INNER JOIN prestamos p ON p.id = cp.prestamo_id
                INNER JOIN clientes c ON c.id = p.cliente_id
                WHERE cp.estado IN ("pendiente", "parcial")
                AND cp.fecha_vencimiento < CURDATE()
                AND NOT EXISTS (
                    SELECT 1
                    FROM notificaciones n
                    WHERE n.cuota_id = cp.id
                        AND n.asunto = "Recordatorio de cuota"
                )';

        return Database::connection()->exec($sql);
    }

    public function borrarNotificacion (){
        $sql = ' DELETE n
            FROM notificaciones n
            INNER JOIN cuotas_prestamo c ON c.id = n.cuota_id
            WHERE c.estado = "pagada"
        ';

        return Database::connection()->exec($sql);
    }

    public function calcularMora (){
        $connection = Database::connection();

        $sqlGetCuotasVencidas = "
            SELECT p.id as idPrestamo, p.tasa_mora_diaria, c.id as idCuota, c.capital_programado, c.interes_programado, 
                c.capital_pagado, c.interes_pagado, c.mora_acumulada, c.mora_pagada, c.monto_programado, c.saldo_cuota 
            FROM prestamos p
            INNER JOIN cuotas_prestamo c ON c.prestamo_id = p.id
            WHERE c.fecha_vencimiento < CURDATE() AND c.estado != 'pagada' AND (
                c.fecha_ultimo_calculo_mora IS NULL
                OR c.fecha_ultimo_calculo_mora < CURDATE()
            )
        ";

        $stmt = $connection->prepare($sqlGetCuotasVencidas);
        $stmt->execute();

        $cuotasVencidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($cuotasVencidas as $cuota){
            $tasaMora = $cuota['tasa_mora_diaria'];
            $saldoCuota = $cuota['saldo_cuota'];
            $moraDiaria = $saldoCuota*($tasaMora/100);

            $sqlUpdateCuota = $connection->prepare ("
                UPDATE cuotas_prestamo
                SET mora_acumulada = mora_acumulada + :mora_acumulada, fecha_ultimo_calculo_mora = CURDATE(), estado = :estado
                WHERE id = :id 
            ");

            $sqlUpdateCuota->execute([
                'mora_acumulada' => $moraDiaria,
                'estado' => 'vencida',
                'id' => $cuota['idCuota']
            ]);

            $sqlUpdatePrestamo = $connection->prepare("
                UPDATE prestamos
                SET total_mora = total_mora + :mora, saldo_pendiente = saldo_pendiente + :mora_a, estado = :estado
                WHERE id = :id
            ");

            $sqlUpdatePrestamo->execute([
                'mora' => $moraDiaria,
                'mora_a' => $moraDiaria,
                'estado' => 'moroso',
                'id' => $cuota['idPrestamo']
            ]);
        }
    }

    public function cambiarEstadoCuota() : bool {

        $sql = "
            UPDATE cuotas_prestamo
            SET estado = 'vencida'
            WHERE fecha_vencimiento < CURDATE() AND estado != 'pagada'
        ";

        return Database::connection()->exec($sql);
    }
}

class NotificationUserRepository
{
    public function notifyAdminNewPaymentRequest(
        int $prestamoId,
        int $solicitudCobroId,
        int $usuarioSolicitanteId,
        float $monto
    ): int {
        $connection = Database::connection();

        // Obtener usuario que realizó la solicitud
        $userStatement = $connection->prepare(
            'SELECT usuario
             FROM usuarios
             WHERE id = :id
             LIMIT 1'
        );

        $userStatement->execute([
            'id' => $usuarioSolicitanteId,
        ]);

        $solicitante = $userStatement->fetch(PDO::FETCH_ASSOC);

        if (!$solicitante) {
            throw new \RuntimeException(
                'No se encontró el usuario que realizó la solicitud.'
            );
        }

        // Buscar únicamente al administrador total
        $adminStatement = $connection->query(
            'SELECT u.id
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE r.nombre = "administrador total"
             AND u.estado = "activo"'
        );

        $admins = $adminStatement->fetchAll(PDO::FETCH_COLUMN);

        if (!$admins) {
            return 0;
        }

        // Preparar inserción de notificación
        $notificationStatement = $connection->prepare(
            'INSERT INTO notificaciones_usuario
             (
                 usuario_id,
                 tipo,
                 titulo,
                 mensaje,
                 prestamo_id,
                 solicitud_cobro_id
             )
             VALUES
             (
                 :usuario_id,
                 :tipo,
                 :titulo,
                 :mensaje,
                 :prestamo_id,
                 :solicitud_cobro_id
             )'
        );

        $count = 0;

        foreach ($admins as $adminId) {
            $notificationStatement->execute([
                'usuario_id' => (int) $adminId,
                'tipo' => 'solicitud_cobro',
                'titulo' => 'Nueva solicitud de cobro',
                'mensaje' => sprintf(
                    '%s solicita un cobro de S/ %.2f.',
                    $solicitante['usuario'],
                    $monto
                ),
                'prestamo_id' => $prestamoId,
                'solicitud_cobro_id' => $solicitudCobroId,
            ]);

            $count++;
        }

        return $count;
    }

    public function getForUser(int $userId): array
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'SELECT
                n.id,
                n.tipo,
                n.titulo,
                n.mensaje,
                n.prestamo_id,
                n.solicitud_cobro_id,
                n.leida,
                n.creado_en,
                n.leida_en
            FROM notificaciones_usuario n
            WHERE n.usuario_id = :usuario_id
            AND n.archivada = 0
            ORDER BY n.creado_en DESC
            LIMIT 20'
        );

        $statement->execute([
            'usuario_id' => $userId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(int $userId): int
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'SELECT COUNT(*)
            FROM notificaciones_usuario
            WHERE usuario_id = :usuario_id
            AND leida = 0'
        );

        $statement->execute([
            'usuario_id' => $userId,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'UPDATE notificaciones_usuario
            SET
                leida = 1,
                leida_en = NOW()
            WHERE id = :id
            AND usuario_id = :usuario_id
            AND leida = 0
            LIMIT 1'
        );

        $statement->execute([
            'id' => $notificationId,
            'usuario_id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function getActiveAdministratorIds(): array
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'SELECT u.id
            FROM usuarios u
            INNER JOIN roles r
                ON r.id = u.rol_id
            WHERE u.estado = "activo"
            AND r.nombre = "administrador total"'
        );

        $statement->execute();

        return array_map(
            'intval',
            $statement->fetchAll(PDO::FETCH_COLUMN)
        );
    }
}

class PushSubscriptionRepository
{
    /**
     * Guarda o actualiza una suscripción Push.
     */
    public function save(
        int $userId,
        string $endpoint,
        string $p256dh,
        string $auth
    ): void {
        $connection = Database::connection();

        // Verificamos si esta suscripción ya existe.
        $statement = $connection->prepare(
            'SELECT id
             FROM dispositivos_push
             WHERE endpoint = :endpoint
             LIMIT 1'
        );

        $statement->execute([
            'endpoint' => $endpoint,
        ]);

        $existingId = $statement->fetchColumn();

        if ($existingId !== false) {

            $updateStatement = $connection->prepare(
                'UPDATE dispositivos_push
                 SET
                     usuario_id = :usuario_id,
                     p256dh = :p256dh,
                     auth = :auth
                 WHERE id = :id
                 LIMIT 1'
            );

            $updateStatement->execute([
                'usuario_id' => $userId,
                'p256dh' => $p256dh,
                'auth' => $auth,
                'id' => (int) $existingId,
            ]);

            return;
        }

        $insertStatement = $connection->prepare(
            'INSERT INTO dispositivos_push
             (
                 usuario_id,
                 endpoint,
                 p256dh,
                 auth
             )
             VALUES
             (
                 :usuario_id,
                 :endpoint,
                 :p256dh,
                 :auth
             )'
        );

        $insertStatement->execute([
            'usuario_id' => $userId,
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth,
        ]);
    }

    /**
     * Elimina una suscripción Push.
     */
    public function deleteByEndpoint(string $endpoint): bool
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'DELETE FROM dispositivos_push
             WHERE endpoint = :endpoint'
        );

        $statement->execute([
            'endpoint' => $endpoint,
        ]);

        return $statement->rowCount() > 0;
    }

    /**
     * Obtiene todas las suscripciones Push de un usuario.
     */
    public function getByUserId(int $userId): array
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'SELECT
                id,
                usuario_id,
                endpoint,
                p256dh,
                auth
            FROM dispositivos_push
            WHERE usuario_id = :usuario_id
            ORDER BY id ASC'
        );

        $statement->execute([
            'usuario_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    /**
     * Elimina una suscripción por su ID.
     */
    public function deleteById(int $id): bool
    {
        $connection = Database::connection();

        $statement = $connection->prepare(
            'DELETE FROM dispositivos_push
            WHERE id = :id
            LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        return $statement->rowCount() > 0;
    }
}
