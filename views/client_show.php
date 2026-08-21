<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex align-items-start gap-4">
                    <div>
                        <?php if (!empty($client['foto'])): ?>
                            <img src="<?= e(app_url('../storage/uploads/' . $client['foto'])) ?>" alt="Cliente" class="rounded-4 object-fit-cover" width="120" height="120">
                        <?php else: ?>
                            <div class="bg-secondary-subtle rounded-4 d-flex align-items-center justify-content-center" style="width:120px;height:120px;">
                                <i class="bi bi-person fs-1 text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="h4 mb-1"><?= e($client['nombres']) ?></h2>
                        <p class="text-secondary mb-3"><?= e($client['dni']) ?> · <?= e($client['telefono']) ?></p>
                        <div class="row g-3 small">
                            <div class="col-md-6"><strong>Correo:</strong> <?= e($client['email'] ?: '-') ?></div>
                            <div class="col-md-6"><strong>Nacionalidad:</strong> <?= e($client['nacionalidad']) ?></div>
                            <div class="col-md-6"><strong>Direccion:</strong> <?= e($client['direccion']) ?></div>
                            <div class="col-md-6"><strong>Vivienda:</strong> <?= e($client['tipo_vivienda']) ?></div>
                            <div class="col-md-6"><strong>Situacion laboral:</strong> <?= e($client['situacion_laboral']) ?></div>
                            <div class="col-md-6"><strong>Estado civil:</strong> <?= e($client['estado_civil']) ?></div>
                            <div class="col-12"><strong>Centro de trabajo:</strong> <?= e($client['direccion_trabajo'] ?: '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card card-soft">
            <div class="card-body">
                <h2 class="h5 mb-3">Garante</h2>
                <?php if (!empty($client['garante_foto'])): ?>
                    <img src="<?= e(app_url('../storage/uploads/' . $client['garante_foto'])) ?>" alt="Garante" class="rounded-4 object-fit-cover mb-3" width="100%" height="220">
                <?php endif; ?>
                <div class="small"><strong>Nombre:</strong> <?= e($client['garante_nombre_completo'] ?: '-') ?></div>
                <div class="small"><strong>DNI:</strong> <?= e($client['garante_dni'] ?: '-') ?></div>
                <div class="small"><strong>Telefono:</strong> <?= e($client['garante_telefono'] ?: '-') ?></div>
                <div class="small"><strong>Direccion:</strong> <?= e($client['garante_direccion'] ?: '-') ?></div>
            </div>
        </div>
    </div>
</div>
