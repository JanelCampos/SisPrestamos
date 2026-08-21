<form method="post" action="<?= e($action) ?>" class="row g-4" id="loan-form">
    <?= csrf_field() ?>
    <div class="col-12 col-xl-8">
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Datos del prestamo</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= e((string) $client['id']) ?>" <?= old('cliente_id') == $client['id'] ? 'selected' : '' ?>><?= e($client['nombres'] . ' - ' . $client['dni']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Producto</label>
                        <select name="producto_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($products as $product): ?>
                                <option
                                    value="<?= e((string) $product['id']) ?>"
                                    data-rate-type="<?= e($product['tasa_interes_tipo']) ?>"
                                    data-rate-value="<?= e((string) $product['tasa_interes_valor']) ?>"
                                    data-late="<?= e((string) $product['tasa_mora_diaria']) ?>"
                                    data-frequency="<?= e($product['frecuencia_pago']) ?>"
                                    <?= old('producto_id') == $product['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($product['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monto principal</label>
                        <input type="number" step="0.01" min="0" name="monto_principal" class="form-control" required value="<?= e(old('monto_principal')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo de tasa</label>
                        <select name="tasa_interes_tipo" class="form-select" required>
                            <?php foreach (['mensual', 'anual'] as $type): ?>
                                <option value="<?= e($type) ?>" <?= old('tasa_interes_tipo') === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tasa de interes</label>
                        <input type="number" step="0.0001" min="0" name="tasa_interes_valor" class="form-control" required value="<?= e(old('tasa_interes_valor')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tasa mora diaria %</label>
                        <input type="number" step="0.0001" min="0" name="tasa_mora_diaria" class="form-control" required value="<?= e(old('tasa_mora_diaria')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha de otorgamiento</label>
                        <input type="date" name="fecha_otorgamiento" class="form-control" required value="<?= e(old('fecha_otorgamiento', date('Y-m-d'))) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Primer pago</label>
                        <input type="date" name="fecha_primer_pago" class="form-control" required value="<?= e(old('fecha_primer_pago')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Plazo de cuotas</label>
                        <input type="number" min="1" name="plazo_cuotas" class="form-control" required value="<?= e(old('plazo_cuotas', '6')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Frecuencia</label>
                        <select name="frecuencia_pago" class="form-select" required>
                            <?php foreach (['diario', 'semanal', 'quincenal', 'mensual'] as $frequency): ?>
                                <option value="<?= e($frequency) ?>" <?= old('frecuencia_pago', 'mensual') === $frequency ? 'selected' : '' ?>><?= e(ucfirst($frequency)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"><?= e(old('observaciones')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Simulacion</h2>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="simulate-loan">Calcular</button>
                </div>
                <div id="simulation-summary" class="small text-secondary mb-3">Completa los datos y calcula el cronograma.</div>
                <div class="table-responsive" style="max-height: 360px;">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vence</th>
                                <th>Capital</th>
                                <th>Interes</th>
                                <th>Cuota</th>
                            </tr>
                        </thead>
                        <tbody id="simulation-body">
                            <tr><td colspan="5" class="text-center text-secondary py-3">Sin simulacion.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= e(app_url('prestamos')) ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar prestamo</button>
        </div>
    </div>
</form>

<script>
    const loanForm = document.getElementById('loan-form');
    const productSelect = loanForm.querySelector('[name="producto_id"]');
    const simulateButton = document.getElementById('simulate-loan');
    const summaryContainer = document.getElementById('simulation-summary');
    const bodyContainer = document.getElementById('simulation-body');

    function syncProductDefaults() {
        const option = productSelect.options[productSelect.selectedIndex];
        if (!option || !option.dataset.rateType) return;
        loanForm.querySelector('[name="tasa_interes_tipo"]').value = option.dataset.rateType;
        loanForm.querySelector('[name="tasa_interes_valor"]').value = option.dataset.rateValue;
        loanForm.querySelector('[name="tasa_mora_diaria"]').value = option.dataset.late;
        loanForm.querySelector('[name="frecuencia_pago"]').value = option.dataset.frequency;
    }

    productSelect.addEventListener('change', syncProductDefaults);

    simulateButton.addEventListener('click', async () => {
        const formData = new FormData(loanForm);
        try {
            const response = await fetch('<?= e(app_url('api/prestamos/simular')) ?>', {
                method: 'POST',
                body: formData
            });
            const payload = await response.json();
            if (!payload.ok) {
                summaryContainer.innerHTML = `<span class="text-danger">${payload.message}</span>`;
                return;
            }

            const summary = payload.data.resumen;
            summaryContainer.innerHTML = `
                <div class="mb-1"><strong>Monto cuota:</strong> <?= e(config('app.currency')) ?> ${Number(summary.monto_cuota).toFixed(2)}</div>
                <div class="mb-1"><strong>Total interes:</strong> <?= e(config('app.currency')) ?> ${Number(summary.total_interes).toFixed(2)}</div>
                <div><strong>Total pagar:</strong> <?= e(config('app.currency')) ?> ${Number(summary.total_pagar).toFixed(2)}</div>
            `;

            bodyContainer.innerHTML = payload.data.cronograma.map((item) => `
                <tr>
                    <td>${item.numero_cuota}</td>
                    <td>${item.fecha_vencimiento}</td>
                    <td><?= e(config('app.currency')) ?> ${Number(item.capital_programado).toFixed(2)}</td>
                    <td><?= e(config('app.currency')) ?> ${Number(item.interes_programado).toFixed(2)}</td>
                    <td><?= e(config('app.currency')) ?> ${Number(item.monto_programado).toFixed(2)}</td>
                </tr>
            `).join('');
        } catch (error) {
            summaryContainer.innerHTML = '<span class="text-danger">No se pudo calcular la simulacion.</span>';
        }
    });

    syncProductDefaults();
</script>
