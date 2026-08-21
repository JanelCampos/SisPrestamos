<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Configuracion de aplicacion</h2>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8"><?= e($config['app']['name']) ?></dd>
                    <dt class="col-sm-4">Base URL</dt>
                    <dd class="col-sm-8"><?= e($config['app']['base_url']) ?></dd>
                    <dt class="col-sm-4">Zona horaria</dt>
                    <dd class="col-sm-8"><?= e($config['app']['timezone']) ?></dd>
                    <dt class="col-sm-4">Moneda</dt>
                    <dd class="col-sm-8"><?= e($config['app']['currency']) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Canales de notificacion</h2>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">SMTP host</dt>
                    <dd class="col-sm-8"><?= e($config['notifications']['mail']['host']) ?></dd>
                    <dt class="col-sm-4">Remitente</dt>
                    <dd class="col-sm-8"><?= e($config['notifications']['mail']['from_email']) ?></dd>
                    <dt class="col-sm-4">Proveedor SMS</dt>
                    <dd class="col-sm-8"><?= e($config['notifications']['sms']['provider']) ?></dd>
                    <dt class="col-sm-4">Emisor SMS</dt>
                    <dd class="col-sm-8"><?= e($config['notifications']['sms']['sender']) ?></dd>
                </dl>
                <div class="alert alert-warning mt-3 mb-0">
                    Ajusta estos valores antes de habilitar correos o SMS en produccion.
                </div>
            </div>
        </div>
    </div>
</div>
