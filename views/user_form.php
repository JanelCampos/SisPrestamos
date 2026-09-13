<?php
$user = $usuario ?? [];
$roles = $roles ?? [];
$esEdicion = !empty($user);
?>

<div class="row justify-content-center">

    <div class="col-12 col-lg-8">

        <div class="card card-soft">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <h2 class="h5 mb-1">
                            <?= e($title) ?>
                        </h2>

                        <p class="text-secondary mb-0">
                            Completa los datos del usuario.
                        </p>
                    </div>

                    <i class="bi bi-person-plus fs-3 text-primary"></i>

                </div>

                <form
                    method="post"
                    action="<?= e($action) ?>"
                >

                    <?= csrf_field() ?>

                    <div class="row g-3">

                        <div class="col-12">

                            <label
                                for="nombre_completo"
                                class="form-label"
                            >
                                Nombre completo
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nombre_completo"
                                name="nombre_completo"
                                value="<?= e($user['nombre_completo'] ?? old('nombre_completo')) ?>"
                                maxlength="150"
                                required
                            >

                        </div>

                        <div class="col-12 col-md-6">

                            <label
                                for="usuario"
                                class="form-label"
                            >
                                Usuario
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="usuario"
                                name="usuario"
                                value="<?= e($user['usuario'] ?? old('usuario')) ?>"
                                maxlength="50"
                                required
                            >

                        </div>

                        <div class="col-12 col-md-6">

                            <label
                                for="telefono"
                                class="form-label"
                            >
                                Teléfono
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="telefono"
                                name="telefono"
                                value="<?= e($user['telefono'] ?? old('telefono')) ?>"
                                maxlength="20"
                            >

                        </div>

                        <div class="col-12">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Correo electrónico
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?= e($user['email'] ?? old('email')) ?>"
                                maxlength="120"
                                required
                            >

                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Contraseña
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                minlength="6"
                                <?= !$esEdicion ? 'required' : '' ?>
                            >

                            <div class="form-text">
                                <?= $esEdicion
                                    ? 'Déjalo vacío si no deseas cambiar la contraseña.'
                                    : 'La contraseña debe tener al menos 6 caracteres.'
                                ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmacion" class="form-label">
                                Confirmar contraseña
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmacion"
                                name="password_confirmacion"
                                minlength="6"
                                <?= !$esEdicion ? 'required' : '' ?>
                            >
                        </div>

                        <div class="col-12 col-md-6">

                            <label
                                for="rol_id"
                                class="form-label"
                            >
                                Rol
                            </label>

                            <select
                                class="form-select"
                                id="rol_id"
                                name="rol_id"
                                required
                            >

                                <option value="">
                                    Selecciona un rol
                                </option>

                                <?php foreach ($roles as $role): ?>

                                    <option
                                        value="<?= e($role['id']) ?>"
                                        <?= (string) ($user['rol_id'] ?? old('rol_id')) === (string) $role['id'] ? 'selected' : '' ?>
                                    >
                                        <?= e($role['nombre']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-12 col-md-6">
                            <?php
                            $estadoActual = $user['estado'] ?? old('estado', 'activo');
                            ?>

                            <label
                                for="estado"
                                class="form-label"
                            >
                                Estado
                            </label>

                            <select
                                class="form-select"
                                id="estado"
                                name="estado"
                                required
                            >

                                <option
                                    value="activo"
                                    <?= $estadoActual === 'activo' ? 'selected' : '' ?>
                                >
                                    Activo
                                </option>

                                <option
                                    value="inactivo"
                                    <?= $estadoActual === 'inactivo' ? 'selected' : '' ?>
                                >
                                    Inactivo
                                </option>

                                <option
                                    value="bloqueado"
                                    <?= $estadoActual === 'bloqueado' ? 'selected' : '' ?>
                                >
                                    Bloqueado
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <a
                            href="<?= e(app_url('usuarios')) ?>"
                            class="btn btn-outline-secondary"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-person-plus me-1"></i>
                            Crear usuario
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>