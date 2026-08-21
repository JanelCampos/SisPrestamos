<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Iniciar sesion') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(240,162,2,.18), transparent 30%),
                radial-gradient(circle at bottom left, rgba(11,57,84,.16), transparent 35%),
                linear-gradient(145deg, #eff4f9 0%, #f9fbfd 100%);
        }
        .login-card {
            max-width: 460px;
            border: 0;
            border-radius: 1.5rem;
            box-shadow: 0 28px 50px rgba(11,57,84,.14);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    <div class="card login-card w-100">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                    <span class="fs-3 fw-bold">SP</span>
                </div>
                <h1 class="h3 mb-2"><?= e(config('app.name')) ?></h1>
                <p class="text-secondary mb-0">Accede al panel de gestion de prestamos y cobranza.</p>
            </div>

            <?php foreach ($flashMessages as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?= e($type) ?>"><?= e($message) ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <form method="post" action="<?= e(app_url('login')) ?>" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-12">
                    <label class="form-label">Usuario o correo</label>
                    <input type="text" name="login" value="<?= e(old('login')) ?>" class="form-control form-control-lg" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Contrasena</label>
                    <input type="password" name="password" class="form-control form-control-lg" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg w-100">Entrar al sistema</button>
                </div>
            </form>

            <div class="mt-4 small text-secondary">
                Credenciales iniciales: <strong>admin</strong> / <strong>Admin123*</strong>
            </div>
        </div>
    </div>
</body>
</html>
