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
                        <input type="text" name="dni" class="form-control" required value="<?= e($client['dni'] ?? old('dni')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="telefono" class="form-control" required value="<?= e($client['telefono'] ?? old('telefono')) ?>">
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
                        <input type="text" name="tipo_vivienda" class="form-control" required value="<?= e($client['tipo_vivienda'] ?? old('tipo_vivienda')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Situacion laboral</label>
                        <input type="text" name="situacion_laboral" class="form-control" required value="<?= e($client['situacion_laboral'] ?? old('situacion_laboral')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado civil</label>
                        <input type="text" name="estado_civil" class="form-control" required value="<?= e($client['estado_civil'] ?? old('estado_civil')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direccion del centro de trabajo</label>
                        <input type="text" name="direccion_trabajo" class="form-control" value="<?= e($client['direccion_trabajo'] ?? old('direccion_trabajo')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Latitud</label>
                        <input type="text" name="latitud" class="form-control" value="<?= e($client['latitud'] ?? old('latitud')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Longitud</label>
                        <input type="text" name="longitud" class="form-control" value="<?= e($client['longitud'] ?? old('longitud')) ?>">
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
                        <input type="text" name="garante_dni" class="form-control" required value="<?= e($client['garante_dni'] ?? old('garante_dni')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="garante_telefono" class="form-control" required value="<?= e($client['garante_telefono'] ?? old('garante_telefono')) ?>">
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
