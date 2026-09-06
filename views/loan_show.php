<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge text-bg-primary mb-2"><?= e($loan['numero_prestamo']) ?></span>
                        <h2 class="h4 mb-1"><?= e($loan['cliente']) ?></h2>
                        <div class="text-secondary small"><?= e($loan['producto']) ?></div>
                    </div>
                    <span class="badge text-bg-<?= e($loan['estado'] === 'pagado' ? 'success' : ($loan['estado'] === 'moroso' ? 'danger' : 'primary')) ?>">
                        <?= e(ucfirst($loan['estado'])) ?>
                    </span>
                </div>
                <div class="row g-3 small">
                    <div class="col-6"><strong>Monto:</strong> <?= e(money($loan['monto_principal'])) ?></div>
                    <div class="col-6"><strong>Saldo:</strong> <?= e(money($loan['saldo_pendiente'])) ?></div>
                    <div class="col-6"><strong>Interes total:</strong> <?= e(money($loan['total_interes'])) ?></div>
                    <div class="col-6"><strong>Mora total:</strong> <?= e(money($loan['total_mora'])) ?></div>
                    <div class="col-6"><strong>Otorgado:</strong> <?= e(format_date($loan['fecha_otorgamiento'])) ?></div>
                    <div class="col-6"><strong>Primer pago:</strong> <?= e(format_date($loan['fecha_primer_pago'])) ?></div>
                    <div class="col-12"><strong>Cliente:</strong> <?= e($loan['dni']) ?> · <?= e($loan['telefono']) ?></div>
                    <div class="col-12"><strong>Direccion:</strong> <?= e($loan['direccion']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-8">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Registrar pago</h2>
                    <small class="text-secondary">Aplica automaticamente mora, interes y capital.</small>
                </div>
                <form method="post" action="<?= e(app_url('prestamos/' . $loan['id'] . '/pago')) ?>" class="row g-3" id='formSolicitudCobro'>
                    <?= csrf_field() ?>
                    <div class="col-md-3">
                        <label class="form-label">Monto recibido</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="monto_recibido"
                            class="form-control"
                            value="<?= $paymentRequest && $paymentRequest['estado'] === 'aprobada'
                                ? e($paymentRequest['monto_recibido'])
                                : '' ?>"
                            <?= $paymentRequest && $paymentRequest['estado'] === 'aprobada' ? 'readonly' : '' ?>
                            required
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Método de pago</label>
                        <select
                            name="metodo_pago"
                            class="form-select"
                            <?= $paymentRequest && $paymentRequest['estado'] === 'aprobada' ? 'disabled' : '' ?>
                            required
                        >
                            <option value="efectivo"
                                <?= ($paymentRequest && $paymentRequest['estado'] === 'aprobada' && $paymentRequest['metodo_pago'] === 'efectivo') ? 'selected' : '' ?>>
                                Efectivo
                            </option>

                            <option value="transferencia"
                                <?= ($paymentRequest && $paymentRequest['estado'] === 'aprobada' && $paymentRequest['metodo_pago'] === 'transferencia') ? 'selected' : '' ?>>
                                Transferencia
                            </option>

                            <option value="yape"
                                <?= ($paymentRequest && $paymentRequest['estado'] === 'aprobada' && $paymentRequest['metodo_pago'] === 'yape') ? 'selected' : '' ?>>
                                Yape
                            </option>

                            <option value="deposito"
                                <?= ($paymentRequest && $paymentRequest['estado'] === 'aprobada' && $paymentRequest['metodo_pago'] === 'deposito') ? 'selected' : '' ?>>
                                Depósito
                            </option>
                        </select>

                        <?php if ($paymentRequest && $paymentRequest['estado'] === 'aprobada'): ?>
                            <input
                                type="hidden"
                                name="metodo_pago"
                                value="<?= e($paymentRequest['metodo_pago']) ?>"
                            >
                        <?php endif; ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha de pago</label>
                        <input
                            type="datetime-local"
                            name="fecha_pago"
                            class="form-control"
                            value="<?= e(date('Y-m-d\TH:i')) ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Observación</label>
                        <input
                            type="text"
                            name="observacion"
                            class="form-control"
                            value="<?= $paymentRequest && $paymentRequest['estado'] === 'aprobada'
                                ? e($paymentRequest['observacion'] ?? '')
                                : '' ?>"
                            placeholder="Pago parcial, refinanciación, etc."
                        >
                    </div>

                    <div class="col-12 text-end">

                        <?php if ($user['role_name'] === 'cobrador'): ?>

                            <?php if ($loan['estado'] === 'pagado'): ?>

                                <!-- No mostrar ningún botón -->

                            <?php elseif (!$paymentRequest): ?>

                                <button
                                    type="button"
                                    class="btn btn-warning"
                                    id="btnSolicitarAutorizacion"
                                >
                                    Solicitar autorización
                                </button>

                            <?php elseif ($paymentRequest['estado'] === 'pendiente'): ?>

                                <div class="text-warning mb-2">
                                    <strong>Solicitud pendiente de autorización</strong>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    disabled
                                >
                                    Esperando autorización
                                </button>

                            <?php elseif ($paymentRequest['estado'] === 'rechazada'): ?>

                                <div class="text-danger mb-2">
                                    <strong>Solicitud rechazada</strong>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-warning"
                                    id="btnSolicitarAutorizacion"
                                >
                                    Solicitar nuevamente
                                </button>

                            <?php elseif ($paymentRequest['estado'] === 'aprobada'): ?>

                                <div class="text-success mb-2">
                                    <strong>Solicitud aprobada</strong>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Registrar cobro
                                </button>

                            <?php elseif ($paymentRequest['estado'] === 'utilizada'): ?>

                                <button
                                    type="button"
                                    class="btn btn-warning"
                                    id="btnSolicitarAutorizacion"
                                >
                                    Solicitar autorización
                                </button>

                            <?php endif; ?>

                        <?php else: ?>

                            <?php if ($loan['estado'] !== 'pagado'): ?>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Registrar cobro
                                </button>

                            <?php endif; ?>

                        <?php endif; ?>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-xl-7">
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Cronograma de cuotas</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vencimiento</th>
                                <th>Capital</th>
                                <th>Interes</th>
                                <th>Mora</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loan['cuotas'] as $quota): 
                                $capitalRestante = $quota['capital_programado'] - $quota['capital_pagado'];
                                $interesRestante = $quota['interes_programado'] - $quota['interes_pagado'];
                                $moraRestante = $quota['mora_acumulada'] - $quota['mora_pagada'];
                                $saldoRestante = $quota['saldo_cuota'] + $moraRestante;
                                ?>
                                <tr>
                                    <td><?= e((string) $quota['numero_cuota']) ?></td>
                                    <td><?= e(format_date($quota['fecha_vencimiento'])) ?></td>
                                    <td><?= e(money($capitalRestante)) ?></td>
                                    <td><?= e(money($interesRestante)) ?></td>
                                    <td><?= e(money($moraRestante)) ?></td>
                                    <td><?= e(money($saldoRestante)) ?></td>
                                    <td><span class="badge text-bg-light"><?= e(ucfirst($quota['estado'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card card-soft mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Pagos realizados</h2>
                <div class="table-responsive" style="max-height: 280px;">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Recibo</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Metodo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($loan['pagos']): ?>
                                <?php foreach ($loan['pagos'] as $payment): ?>
                                    <tr>
                                        <td><?= e($payment['numero_recibo']) ?></td>
                                        <td><?= e(format_date($payment['fecha_pago'], 'd/m/Y H:i')) ?></td>
                                        <td><?= e(money($payment['monto_recibido'])) ?></td>
                                        <td><?= e(ucfirst($payment['metodo_pago'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-secondary py-3">Sin pagos registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Historial de movimientos</h2>
                <div class="timeline small">
                    <?php if ($loan['movimientos']): ?>
                        <?php foreach ($loan['movimientos'] as $movement): ?>
                            <div class="border-start border-2 ps-3 pb-3 ms-2">
                                <div class="fw-semibold"><?= e(ucfirst($movement['tipo_movimiento'])) ?></div>
                                <div class="text-secondary"><?= e($movement['descripcion']) ?></div>
                                <div class="text-secondary small"><?= e($movement['usuario']) ?> · <?= e(format_date($movement['creado_en'], 'd/m/Y H:i')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-secondary">No hay movimientos registrados.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const formSolicitudCobro = document.getElementById('formSolicitudCobro');
    const btnSolicitarAutorizacion = document.getElementById('btnSolicitarAutorizacion');

    if (formSolicitudCobro && btnSolicitarAutorizacion) {
        btnSolicitarAutorizacion.addEventListener('click', function () {

            if (!formSolicitudCobro.checkValidity()) {
                formSolicitudCobro.reportValidity();
                return;
            }

            formSolicitudCobro.action =
                '<?= e(app_url('prestamos/' . $loan['id'] . '/solicitud-cobro')) ?>';

            formSolicitudCobro.submit();
        });
    }
</script>