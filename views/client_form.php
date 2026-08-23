<?php
$client = $client ?? [];
?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="row g-4">
    <?= csrf_field() ?>
    <div class="col-12 col-xl-8">
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Datos del cliente</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombres completos</label>
                        <input type="text" name="nombres" class="form-control" required value="<?= e($client['nombres'] ?? old('nombres')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" required value="<?= e($client['dni'] ?? old('dni')) ?>" maxlength="8">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="telefono" class="form-control" required value="<?= e($client['telefono'] ?? old('telefono')) ?>" maxlength="9">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo electronico</label>
                        <input type="email" name="email" class="form-control" value="<?= e($client['email'] ?? old('email')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nacionalidad</label>
                        <input type="text" name="nacionalidad" class="form-control" required value="<?= e($client['nacionalidad'] ?? old('nacionalidad')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direccion fisica</label>
                        <input type="text" name="direccion" class="form-control" required value="<?= e($client['direccion'] ?? old('direccion')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo de vivienda</label>
                        <select name="tipo_vivienda" id="" class="form-select" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="propia" <?= $client['tipo_vivienda'] === 'propia' ? 'selected' : '' ?>>Propia</option>
                            <option value="alquilada" <?= $client['tipo_vivienda'] === 'alquilada' ? 'selected' : '' ?>>Alquilada</option>
                            <option value="familiar" <?= $client['tipo_vivienda'] === 'familiar' ? 'selected' : '' ?>>De un familiar</option>
                            <option value="cedida" <?= $client['tipo_vivienda'] === 'cedida' ? 'selected' : '' ?>>Cedida</option>
                            <option value="anticresis" <?= $client['tipo_vivienda'] === 'anticresis' ? 'selected' : '' ?>>Anticresis</option>
                            <option value="compartida" <?= $client['tipo_vivienda'] === 'compartida' ? 'selected' : '' ?>>Compartida</option>
                            <option value="otro" <?= $client['tipo_vivienda'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Situacion laboral</label>
                        <select name="situacion_laboral" id="" class="form-select" required>
                            <option value="">Seleccionar situacion laboral</option>
                            <option value="dependiente" <?= $client['situacion_laboral'] === 'dependiente' ? 'selected' : '' ?>>Trabajador dependiente</option>
                            <option value="independiente" <?= $client['situacion_laboral'] === 'independiente' ? 'selected' : '' ?>>Trabajador independiente</option>
                            <option value="empresario" <?= $client['situacion_laboral'] === 'empresario' ? 'selected' : '' ?>>Empresario / Negocio propio</option>
                            <option value="informal" <?= $client['situacion_laboral'] === 'informal' ? 'selected' : '' ?>>Trabajo informal</option>
                            <option value="jubilado" <?= $client['situacion_laboral'] === 'jubilado' ? 'selected' : '' ?>>Jubilado / Pensionista</option>
                            <option value="desempleado" <?= $client['situacion_laboral'] === 'desempleado' ? 'selected' : '' ?>>Desempleado</option>
                            <option value="estudiante" <?= $client['situacion_laboral'] === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                            <option value="otro" <?= $client['situacion_laboral'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado civil</label>
                        <select name="estado_civil" id="" class="form-select" required>
                            <option value="">Seleccionar estado civil</option>
                            <option value="soltero" <?= $client['estado_civil'] === 'soltero' ? 'selected' : '' ?>>Soltero(a)</option>
                            <option value="casado" <?= $client['estado_civil'] === 'casado' ? 'selected' : '' ?>>Casado(a)</option>
                            <option value="conviviente" <?= $client['estado_civil'] === 'conviviente' ? 'selected' : '' ?>>Conviviente</option>
                            <option value="divorciado" <?= $client['estado_civil'] === 'divorciado' ? 'selected' : '' ?>>Divorciado(a)</option>
                            <option value="separado" <?= $client['estado_civil'] === 'separado' ? 'selected' : '' ?>>Separado(a)</option>
                            <option value="viudo" <?= $client['estado_civil'] === 'viudo' ? 'selected' : '' ?>>Viudo(a)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direccion del centro de trabajo</label>
                        <input type="text" name="direccion_trabajo" class="form-control" value="<?= e($client['direccion_trabajo'] ?? old('direccion_trabajo')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fotografia</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <input type="hidden" name="foto_actual" value="<?= e($client['foto'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Datos del garante</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="garante_nombre_completo" class="form-control" required value="<?= e($client['garante_nombre_completo'] ?? old('garante_nombre_completo')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">DNI</label>
                        <input type="text" name="garante_dni" class="form-control" required value="<?= e($client['garante_dni'] ?? old('garante_dni')) ?>" maxlength="8">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="garante_telefono" class="form-control" required value="<?= e($client['garante_telefono'] ?? old('garante_telefono')) ?>" maxlength="9">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direccion</label>
                        <input type="text" name="garante_direccion" class="form-control" required value="<?= e($client['garante_direccion'] ?? old('garante_direccion')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Fotografia</label>
                        <input type="file" name="garante_foto" class="form-control" accept="image/*">
                        <input type="hidden" name="garante_foto_actual" value="<?= e($client['garante_foto'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= e(app_url('clientes')) ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cliente</button>
        </div>
    </div>
</form>
