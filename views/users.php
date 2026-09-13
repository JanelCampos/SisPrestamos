<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Usuarios del sistema</h2>

                    <a
                        href="<?= e(app_url('usuarios/crear')) ?>"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-person-plus me-1"></i>
                        Nuevo usuario
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Telefono</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td><?= e($row['nombre_completo']) ?></td>
                                    <td><?= e($row['usuario']) ?></td>
                                    <td><?= e($row['email']) ?></td>
                                    <td><?= e($row['telefono'] ?: '-') ?></td>
                                    <td><span class="badge badge-soft"><?= e($row['role_name']) ?></span></td>
                                    <td>
                                        <?php if ($row['estado'] === 'activo'): ?>

                                            <span class="badge text-bg-success">
                                                Activo
                                            </span>

                                        <?php elseif ($row['estado'] === 'inactivo'): ?>

                                            <span class="badge text-bg-secondary">
                                                Inactivo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge text-bg-danger">
                                                Bloqueado
                                            </span>

                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a
                                            href="<?= e(app_url('usuarios/editar/' . $row['id'])) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Editar usuario"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ($row['estado'] === 'activo'): ?>

                                            <form
                                                method="POST"
                                                action="<?= e(app_url('usuarios/desactivar/' . $row['id'])) ?>"
                                                class="d-inline"
                                            >
                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Desactivar usuario"
                                                >
                                                    <i class="bi bi-person-dash"></i>
                                                </button>
                                            </form>

                                        <?php else: ?>

                                            <form
                                                method="POST"
                                                action="<?= e(app_url('usuarios/activar/' . $row['id'])) ?>"
                                                class="d-inline"
                                            >
                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Activar usuario"
                                                >
                                                    <i class="bi bi-person-check"></i>
                                                </button>
                                            </form>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card card-soft mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Roles disponibles</h2>
                <ul class="list-group list-group-flush">
                    <?php foreach ($roles as $role): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span><?= e($role['nombre']) ?></span>
                            <span class="text-secondary"><?= e($role['descripcion']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Notificaciones pendientes</h2>
                <?php if ($pendingNotifications): ?>
                    <div class="small d-grid gap-3">
                        <?php foreach ($pendingNotifications as $row): ?>
                            <div class="border rounded-3 p-3">
                                <div class="fw-semibold"><?= e($row['cliente']) ?></div>
                                <div class="text-secondary"><?= e($row['canal']) ?> · <?= e($row['destinatario']) ?></div>
                                <div><?= e($row['asunto']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">Sin notificaciones pendientes.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
