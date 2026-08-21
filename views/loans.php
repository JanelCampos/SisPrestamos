<div class="card card-soft mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach (['vigente', 'pagado', 'vencido', 'moroso'] as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= input('estado') === $estado ? 'selected' : '' ?>><?= e(ucfirst($estado)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']) ?>" <?= input('cliente_id') == $client['id'] ? 'selected' : '' ?>><?= e($client['nombres']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= e(input('fecha_desde', '')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= e(input('fecha_hasta', '')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Vencimiento</label>
                <input type="date" name="vencimiento" class="form-control" value="<?= e(input('vencimiento', '')) ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= e(app_url('prestamos/crear')) ?>" class="btn btn-primary">Registrar prestamo</a>
</div>

<div class="card card-soft">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Cliente</th>
                        <th>Otorgamiento</th>
                        <th>Monto</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($loans): ?>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?= e($loan['numero_prestamo']) ?></td>
                                <td><?= e($loan['cliente']) ?></td>
                                <td><?= e(format_date($loan['fecha_otorgamiento'])) ?></td>
                                <td><?= e(money($loan['monto_principal'])) ?></td>
                                <td><?= e(money($loan['saldo_pendiente'])) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'secondary';
                                    if ($loan['estado'] === 'vigente') $badgeClass = 'primary';
                                    if ($loan['estado'] === 'pagado') $badgeClass = 'success';
                                    if ($loan['estado'] === 'vencido') $badgeClass = 'warning';
                                    if ($loan['estado'] === 'moroso') $badgeClass = 'danger';
                                    ?>
                                    <span class="badge text-bg-<?= e($badgeClass) ?>"><?= e(ucfirst($loan['estado'])) ?></span>
                                </td>
                                <td><?= e($loan['usuario_registro']) ?></td>
                                <td class="text-end">
                                    <a href="<?= e(app_url('prestamos/ver/' . $loan['id'])) ?>" class="btn btn-sm btn-outline-primary">Detalle</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">No hay prestamos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
