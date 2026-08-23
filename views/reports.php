<div class="card card-soft mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Estado cartera</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach (['vigente', 'pagado', 'vencido', 'moroso'] as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= input('estado') === $estado ? 'selected' : '' ?>><?= e(ucfirst($estado)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= e(input('fecha_desde', '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= e(input('fecha_hasta', '')) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Reporte de cartera</h2>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= e(app_url('reportes/cartera/excel?' . http_build_query($_GET))) ?>" class="btn btn-outline-success">Excel</a>
                        <a href="<?= e(app_url('reportes/cartera/pdf?' . http_build_query($_GET))) ?>" class="btn btn-outline-danger" target="_blank">PDF</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Prestamo</th>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($portfolio): ?>
                                <?php foreach ($portfolio as $row): ?>
                                    <tr>
                                        <td><?= e($row['numero_prestamo']) ?></td>
                                        <td><?= e($row['cliente']) ?></td>
                                        <td><?= e(money($row['monto_principal'])) ?></td>
                                        <td><?= e(money($row['saldo_pendiente'])) ?></td>
                                        <td><?= e(ucfirst($row['estado'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-secondary py-3">Sin resultados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Reporte de cobros</h2>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= e(app_url('reportes/cobros/excel?' . http_build_query($_GET))) ?>" class="btn btn-outline-success">Excel</a>
                        <a href="<?= e(app_url('reportes/cobros/pdf?' . http_build_query($_GET))) ?>" class="btn btn-outline-danger" target="_blank">PDF</a>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 360px;">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Recibo</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($collections): ?>
                                <?php foreach ($collections as $row): ?>
                                    <tr>
                                        <td><?= e($row['numero_recibo']) ?></td>
                                        <td><?= e($row['cliente']) ?></td>
                                        <td><?= e(format_date($row['fecha_pago'], 'd/m/Y H:i')) ?></td>
                                        <td><?= e(money($row['monto_recibido'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-secondary py-3">Sin cobros registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
