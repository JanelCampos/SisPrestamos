<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Solicitudes de cobro</h1>
            <p class="text-secondary mb-0">
                Revisa las solicitudes enviadas por los cobradores.
            </p>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">

            <h2 class="h5 mb-3">Solicitudes pendientes</h2>

            <?php if (empty($requests)): ?>

                <div class="alert alert-light border mb-0">
                    No hay solicitudes de cobro pendientes.
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Préstamo</th>
                                <th>Cobrador</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Fecha solicitada</th>
                                <th>Observación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($requests as $request): ?>

                                <tr>
                                    <td>
                                        <?= e($request['numero_prestamo']) ?>
                                    </td>

                                    <td>
                                        <?= e($request['cobrador']) ?>
                                    </td>

                                    <td>
                                        S/ <?= number_format(
                                            (float) $request['monto_recibido'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= e(ucfirst($request['metodo_pago'])) ?>
                                    </td>

                                    <td>
                                        <?= e($request['creado_en']) ?>
                                    </td>

                                    <td>
                                        <?= e($request['observacion'] ?? '') ?>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-warning">
                                            Pendiente
                                        </span>

                                        <div class="mt-2 d-flex gap-1">
                                            <form 
                                                method="post" 
                                                action="<?= e(app_url('solicitudes-cobro/' . $request['id'] . '/aprobar')) ?>"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Aprobar
                                                </button>
                                            </form>

                                            <form 
                                                method="post" 
                                                action="<?= e(app_url('solicitudes-cobro/' . $request['id'] . '/rechazar')) ?>"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Rechazar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>