<?php
$data = $data['data'] ?? [];
$metrics = $data['metrics'] ?? [];
$chartRows = $data['chart'] ?? [];
$alerts = $data['alerts'] ?? [];
?>

<?php if (\App\Auth::hasRole('administrador total')): ?>
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase mb-2">Monto total prestado</div>
                    <div class="kpi-value"><?= e(money($metrics['total_prestado'] ?? 0)) ?></div>
                    <div class="small text-secondary mt-2">Capital desembolsado historico.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase mb-2">Utilidad del mes</div>
                    <div class="kpi-value"><?= e(money($metrics['utilidad_mes'] ?? 0)) ?></div>
                    <div class="small text-secondary mt-2">Intereses y mora recuperados en el mes actual.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase mb-2">Utilidad historica</div>
                    <div class="kpi-value"><?= e(money($metrics['utilidad_total'] ?? 0)) ?></div>
                    <div class="small text-secondary mt-2">Intereses y mora acumulados.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase mb-2">Prestamos vigentes</div>
                    <div class="kpi-value text-primary"><?= e((string) ($metrics['prestamos_vigentes'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase mb-2">Prestamos pagados</div>
                    <div class="kpi-value text-success"><?= e((string) ($metrics['prestamos_pagados'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase mb-2">Prestamos morosos</div>
                    <div class="kpi-value text-danger"><?= e((string) ($metrics['prestamos_morosos'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<div class="row g-4">
    <?php if (\App\Auth::hasRole('administrador total')): ?>
        <div class="col-12 col-xl-8">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-1">Tendencias mensuales</h2>
                            <p class="text-secondary mb-0">Produccion, intereses proyectados y tasa de mora.</p>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-auto">
                                <a
                                    href="<?= e(app_url('prestamos/crear')) ?>"
                                    class="btn btn-primary btn-sm w-100"
                                >
                                    Nuevo prestamo
                                </a>
                            </div>

                            <div class="col-12 col-sm-auto">
                                <a
                                    href="<?= e(app_url('clientes/crear')) ?>"
                                    class="btn btn-outline-primary btn-sm w-100"
                                >
                                    Nuevo cliente
                                </a>
                            </div>
                        </div>
                    </div>
                    <canvas id="dashboardChart" height="200"></canvas>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="col-12 col-xl-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Alertas de cartera</h2>
                <?php if ($alerts): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($alerts as $alert): 
                            $moraRestante = $alert['mora_acumulada'] - $alert['mora_pagada'];
                            $saldoPendiente = $alert['saldo_cuota'] + $moraRestante;
                            ?>
                            <a href="<?= e(app_url('prestamos/ver/' . $alert['id'])) ?>" class="list-group-item list-group-item-action border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold"><?= e($alert['cliente']) ?></div>
                                        <div class="small text-secondary"><?= e($alert['numero_prestamo']) ?> · Vence <?= e(format_date($alert['fecha_vencimiento'])) ?></div>
                                    </div>
                                    <span class="badge text-bg-danger"><?= e(money($saldoPendiente)) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">No hay alertas criticas en este momento.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const dashboardRows = <?= json_encode($chartRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const chartContext = document.getElementById('dashboardChart');

    if (chartContext) {
        new Chart(chartContext, {
            data: {
                labels: dashboardRows.map((row) => row.periodo),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Prestamos otorgados',
                        data: dashboardRows.map((row) => Number(row.prestamos_otorgados)),
                        backgroundColor: 'rgba(11,57,84,0.78)',
                        yAxisID: 'y3',
                        borderRadius: 8
                    },
                    {
                        type: 'line',
                        label: 'Interes proyectado',
                        data: dashboardRows.map((row) => Number(row.interes_proyectado)),
                        borderColor: '#f0a202',
                        backgroundColor: 'rgba(240,162,2,0.16)',
                        yAxisID: 'y1',
                        tension: 0.35
                    },
                    {
                        type: 'line',
                        label: 'Tasa de mora %',
                        data: dashboardRows.map((row) => Number(row.tasa_mora).toFixed(2)),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.12)',
                        yAxisID: 'y2',
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y1: { position: 'right', grid: { drawOnChartArea: false } },
                    y2: { position: 'right', grid: { drawOnChartArea: false }, suggestedMax: 100 }
                }
            }
        });
    }
</script>
