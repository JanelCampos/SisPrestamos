<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label">Buscar cliente</label>
            <input type="text" name="q" class="form-control" value="<?= e(input('q', '')) ?>" placeholder="Nombre, DNI o telefono">
        </div>
        <div class="col-12 col-md-4">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
        </div>
    </form>
    <a href="<?= e(app_url('clientes/crear')) ?>" class="btn btn-primary">Nuevo cliente</a>
</div>

<div class="card card-soft">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th>Contacto</th>
                        <th>Direccion</th>
                        <th>Garante</th>
                        <th>Deuda activa</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clients): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($client['foto'])): ?>
                                        <img src="<?= e(app_url('../storage/uploads/' . $client['foto'])) ?>" alt="Foto" class="rounded-circle object-fit-cover" width="44" height="44">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary-subtle d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($client['nombres']) ?></div>
                                    <small class="text-secondary"><?= e($client['nacionalidad']) ?></small>
                                </td>
                                <td><?= e($client['dni']) ?></td>
                                <td>
                                    <div><?= e($client['telefono']) ?></div>
                                    <small class="text-secondary"><?= e($client['email'] ?: 'Sin correo') ?></small>
                                </td>
                                <td>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($client['direccion']) ?>" target="_blank" rel="noopener noreferrer">
                                        <?= e($client['direccion']) ?>
                                    </a>
                                </td>
                                <td><?= e($client['garante_nombre'] ?: '-') ?></td>
                                <td><span class="badge text-bg-light"><?= e(money($client['deuda_activa'])) ?></span></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= e(app_url('clientes/ver/' . $client['id'])) ?>" class="btn btn-outline-secondary">Ver</a>
                                        <a href="<?= e(app_url('clientes/editar/' . $client['id'])) ?>" class="btn btn-outline-primary">Editar</a>
                                        <?php if (\App\Auth::hasRole('administrador total')): ?>
                                            <form method="post" action="<?= e(app_url('clientes/eliminar/' . $client['id'])) ?>" onsubmit="return confirm('Se eliminara de forma segura el cliente.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger">Eliminar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">No hay clientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
